<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\Model;

use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Helper\MemoryInfo;
use Akeeba\Replace\Engine\Core\Helper\OutFileSetup;
use Akeeba\Replace\Engine\Core\Part\Database;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Timer\Timer;
use Akeeba\Replace\WordPress\MVC\Model\Model;

class Replace extends Model
{
	/**
	 * Returns the cached Configuration for the user. If no user is logged in or no valid configuration is cached it
	 * returns the default configuration.
	 *
	 * @return  Configuration
	 */
	public function getCachedConfiguration()
	{
		$user = wp_get_current_user();
		$transient = $this->getConfigTransientForUser($user);

		if (empty($transient))
		{
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
		$config = [
			'outputSQLFile' => '[OUTPUT_PATH][YEAR][MONTH][DAY]_[TIME_TZ]_replace.sql',
			'backupSQLFile' => '[OUTPUT_PATH][YEAR][MONTH][DAY]_[TIME_TZ]_backup.sql',
			'logFile'       => '[OUTPUT_PATH][YEAR][MONTH][DAY]_[TIME_TZ].log',
			'minLogLevel'   => LoggerInterface::SEVERITY_INFO,
		];

		$config = array_merge($config, $overrides);

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
			'OUTPUT_PATH' => plugin_dir_path(AKEEBA_REPLACE_SELF) . 'output',
		];

		// Set up and return a new core engine
		$helper       = new OutFileSetup();
		$timer        = new Timer();
		$db           = $this->getDatabaseDriver();
		$logger       = $helper->makeLogger($configuration, true, $additional);
		$outputWriter = $helper->makeOutputWriter($configuration, true, $additional);
		$backupWriter = $helper->makeBackupWriter($configuration, true, $additional);
		$memoryInfo   = new MemoryInfo();

		return new Database($timer, $db, $logger, $outputWriter, $backupWriter, $configuration, $memoryInfo);
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