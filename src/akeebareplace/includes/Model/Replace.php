<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\Model;

use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Helper\MemoryInfo;
use Akeeba\Replace\Engine\Core\Helper\OutFileSetup;
use Akeeba\Replace\Engine\Core\Part\Database;
use Akeeba\Replace\Engine\ErrorHandling\ErrorException;
use Akeeba\Replace\Engine\PartInterface;
use Akeeba\Replace\Engine\PartStatus;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Timer\Timer;
use Akeeba\Replace\WordPress\Helper\Form;
use Akeeba\Replace\WordPress\MVC\Model\DataModel;
use Akeeba\Replace\WordPress\MVC\Model\Model;

class Replace extends Model
{
	/**
	 * Returns the cached Configuration for the user. If no user is logged in or no valid configuration is cached it
	 * returns the default configuration.
	 *
	 * @param   $reset  bool  Should I reset the cached configuration and return the default values?
	 *
	 * @return  Configuration
	 */
	public function getCachedConfiguration($reset = false)
	{
		$user = wp_get_current_user();
		$transient = $this->getConfigTransientForUser($user);

		if (empty($transient))
		{
			return $this->makeConfiguration();
		}

		if ($reset)
		{
			delete_transient($transient);

			return $this->makeConfiguration();
		}

		$configuration = get_transient($transient);

		if (($configuration === false) || !is_array($configuration))
		{
			return $this->makeConfiguration();
		}

		// Convert the stored configuration array to an object
		return new Configuration($configuration);
	}

	/**
	 * Caches the configuration for the currently logged in user.
	 *
	 * @param   Configuration  $configuration
	 *
	 * @return  void
	 */
	public function setCachedConfiguration(Configuration $configuration)
	{
		$user = wp_get_current_user();
		$transient = $this->getConfigTransientForUser($user);

		if (empty($transient))
		{
			return;
		}

		set_transient($transient, $configuration->toArray(), YEAR_IN_SECONDS);
	}

	/**
	 * Make a configuration object. Uses the defaults for the values not explicitly overridden.
	 *
	 * @param   array  $overrides  Configuration overrides
	 *
	 * @return  Configuration
	 *
	 * @see     Configuration::__construct()
	 */
	public function makeConfiguration($overrides = [])
	{
		global $wpdb;

		$config = [
			'outputSQLFile' => '[OUTPUT_PATH][YEAR][MONTH][DAY]_[TIME_TZ]_replace.sql',
			'backupSQLFile' => '[OUTPUT_PATH][YEAR][MONTH][DAY]_[TIME_TZ]_backup.sql',
			'logFile'       => '[OUTPUT_PATH][YEAR][MONTH][DAY]_[TIME_TZ].log',
			'minLogLevel'   => LoggerInterface::SEVERITY_INFO,
			'excludeTables' => [$wpdb->prefix . 'akeebareplace_jobs'],
			'excludeRows'   => [$wpdb->prefix . 'posts' => ['guid']],
			'description'   => sprintf(__('Replacement job created on %s', 'akeebareplace'), Form::formatDate()),
		];

		$config = array_merge_recursive($config, $overrides);

		return new Configuration($config);
	}

	/**
	 * Make a database replacement engine out of the provided configuration
	 *
	 * @param   Configuration  $configuration
	 *
	 * @return  Database
	 */
	public function makeEngine(Configuration $configuration)
	{
		// Get additional path definitions, used for setting up the file writers
		$additional = [
			'[OUTPUT_PATH]' => plugin_dir_path(AKEEBA_REPLACE_SELF) . 'output/',
		];

		// Load options
		$maxExec     = get_option('akeebareplace_max_execution', 10);
		$runtimeBias = get_option('akeebareplace_runtime_bias', 75);

		// Set up and return a new core engine
		$wpTimezone   = get_option('timezone_string', 'UTC');
		$helper       = new OutFileSetup(null, $wpTimezone);
		$timer        = new Timer($maxExec, $runtimeBias);
		$db           = $this->getDatabaseDriver();
		$logger       = $helper->makeLogger($configuration, true, $additional);
		$outputWriter = $helper->makeOutputWriter($configuration, true, $additional);
		$backupWriter = $helper->makeBackupWriter($configuration, true, $additional);
		$memoryInfo   = new MemoryInfo();

		return new Database($timer, $db, $logger, $outputWriter, $backupWriter, $configuration, $memoryInfo);
	}

	public function stepEngine($startNew = false)
	{
		// Load the saved engine
		/** @var Database $engine */
		$engine = $this->getEngine();

		// If we are starting a new replacement we have to create a new engine instead
		if ($startNew)
		{
			// Create a new engine
			$configuration = $this->getCachedConfiguration();
			$engine        = $this->makeEngine($configuration);
			$engine->getLogger()->debug("===== Starting a new replacement job =====");

			$jobModel    = DataModel::getInstance('Job');
			$description = $configuration->getDescription();

			if (empty($description))
			{
				$description = sprintf(__('Replacement job created on %s', 'akeebareplace'), Form::formatDate());
			}

			$jobID = $jobModel->save([
				'options'     => serialize($engine->getConfig()->toArray()),
				'description' => $description,
				'created_on'  => gmdate('Y-m-d H:i:s'),
				'run_on'      => gmdate('Y-m-d H:i:s'),
			]);

			$engine->getLogger()
			       ->info(sprintf('The new job ID is %u in the database (%s table).', $jobID, $jobModel->getTableName()))
			;
		}
		else
		{
			$engine->getLogger()->debug("===== Continuing the replacement job (new page load) =====");
		}

		// Prime the status with an error -- this is used if we cannot load a cached engine
		$status = new PartStatus([
			'Error' => 'Trying to step the replacement engine after it has finished processing replacements.'
		]);

		$warnings = [];
		$error    = null;

		// Run a few steps if we really do have an engine
		if (!is_null($engine))
		{
			$timer = $engine->getTimer();

			// Run steps while we have time left
			while ($timer->getTimeLeft())
			{
				$engine->getLogger()->debug("----- Ticking the engine (running one more step) -----");

				// Run a single step
				$status = $engine->tick();

				// Merge any warnings
				$newWarnings = $status->getWarnings();
				$warnings    = array_merge($warnings, $newWarnings);

				// Are we done already?
				if ($status->isDone())
				{
					break;
				}

				// Check for an error
				$error = $status->getError();

				if (!is_object($error) || !($error instanceof ErrorException))
				{
					$error = null;

					continue;
				}

				// We hit an error
				break;
			}
		}

		// Construct a new status array with the merged warnings and the carried over error (if any)
		$configArray             = $status->toArray();
		$configArray['Warnings'] = $warnings;
		$configArray['Error']    = $error;
		$status                  = new PartStatus($configArray);

		// If we are done (or died with an error) we set the engine to null; this will unset it from the cache.
		if ($status->isDone() || !is_null($error))
		{
			// Log that we're all done
			$reason = !is_null($error) ? 'error' : 'all done';
			$engine->getLogger()->debug("===== Engine has stopped executing ($reason) =====");
			$engine->getLogger()->debug("Cached engine state will be cleared and the results returned to the caller");

			// Do not move this above the logging lines or you'll get a Fatal Error
			$engine = null;
		}
		else
		{
			$engine->getLogger()->debug('===== Engine paused (will continue in the next page load) =====');
		}

		// Cache the new engine status
		$this->setEngineCache($engine);

		// Enforce minimum execution time but only if we haven't finished already (done or error)
		if (!is_null($engine))
		{
			$minExec     = get_option('akeebareplace_min_execution', 1);
			$runningTime = $timer->getRunningTime();

			if ($runningTime < $minExec)
			{
				$sleepForSeconds = $minExec - $runningTime;
				$engine->getLogger()->debug(sprintf("Applying minimum execution time (sleep for %0.3f seconds)", $sleepForSeconds));
				usleep($sleepForSeconds * 1000000);
			}

			$engine->getLogger()->debug('Caching the engine and returning results to the caller');
		}

		return $status;
	}

	/**
	 * Save the engine state to WordPress' options
	 *
	 * @param   PartInterface|null  $engine
	 */
	public function setEngineCache($engine)
	{
		// Get the saved engine cache
		$current = $this->getEngine(true);

		// Add the current engine state
		$user = wp_get_current_user();
		$current[$user->ID] = $engine;

		// Maybe we were trying to delete the engine cache?
		if (is_null($engine))
		{
			unset($current[$user->ID]);
		}

		// Save the option to the database
		update_option('akeebareplace_engine_cache', $current, false);
	}

	/**
	 * Retrieve the engine from the cache
	 *
	 * @param   bool  $allEntries  Should I retrieve all cached values? False to only retrieve the currently configured engine.
	 *
	 * @return  array|PartInterface|null
	 */
	public function getEngine($allEntries = false)
	{
		$current = get_option('akeebareplace_engine_cache');

		if ($current === false)
		{
			// The option had not been registered yet.
			add_option('akeebareplace_engine_cache', [], false, false);

			$current = [];
		}

		if ($allEntries)
		{
			return $current;
		}

		$user = wp_get_current_user();

		if (!array_key_exists($user->ID, $current))
		{
			return null;
		}

		return $current[$user->ID];
	}

	/**
	 * Return a list of collations supported by the database server in alphabetic order. The most useful collations
	 * (the UTF8MB4 and UTF8 collations used by WordPress the past several years) are listed first.
	 *
	 * @return  array
	 */
	public function getCollations()
	{
		global $wpdb;

		$query = 'SHOW COLLATION';

		$collations = $wpdb->get_col($query);

		if (empty($collations))
		{
			return [];
		}

		asort($collations);

		foreach (['utf8_general_ci', 'utf8mb4_unicode_ci', 'utf8mb4_unicode_520_ci'] as $check)
		{
			$pos = array_search($check, $collations);

			if ($pos === false)
			{
				continue;
			}

			unset($collations[$pos]);

			array_unshift($collations, $check);
		}

		return $collations;
	}

	/**
	 * Get a list of all tables in the database
	 *
	 * @param   bool  $allTables  Should I include all tables? False for just the tables matching the site's prefix.
	 *
	 * @return  array
	 */
	public function getDatabaseTables($allTables = false)
	{
		$db     = $this->getDatabaseDriver();
		$tables = $db->getTableList();

		if ($allTables)
		{
			return $tables;
		}

		$prefix = $db->getPrefix();

		return array_filter($tables, function($table) use ($prefix) {
			return substr($table, 0, strlen($prefix)) == $prefix;
		});
	}

	/**
	 * Returns the configuration caching key (used with WP's transients API) for a specific user
	 *
	 * @param   \WP_User  $user  The WP user object
	 *
	 * @return  string
	 */
	protected function getConfigTransientForUser(\WP_User $user)
	{
		if (!$user->exists())
		{
			return '';
		}

		return 'akeebareplace_last_configuration_' . $user->ID;
	}

	/**
	 * Get an Engine database driver for this WordPress installation
	 *
	 * @return  Driver
	 */
	private function getDatabaseDriver()
	{
		global $wpdb;

		// Find out the correct database driver to use
		$driver = 'WordPressMySQL';

		if ($wpdb->use_mysqli && version_compare(PHP_VERSION, '7.0.0', 'ge'))
		{
			$driver = 'WordPressMySQLi';
		}

		if (!$wpdb->is_mysql)
		{
			throw new \RuntimeException('Akeeba Replace only works with MySQL-compatible databases.');
		}

		return Driver::getInstance([
			'driver' => $driver,
		]);
}
}