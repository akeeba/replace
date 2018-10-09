<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\Controller;

use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Part\Database;
use Akeeba\Replace\Engine\PartStatus;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\WordPress\Model\Replace as ReplaceModel;
use Akeeba\Replace\WordPress\MVC\Controller\Controller;
use Akeeba\Replace\WordPress\View\Replace\Html;

class Replace extends Controller
{
	/**
	 * Executes before the task is loaded and executed.
	 *
	 * @param   string $task The task to execute (passed by reference so we can modify it)
	 */
	public function onBeforeExecute(&$task)
	{
		// The default task in the Replace view is "new" which shows the interface to a new job
		if ($task === 'display')
		{
			$task = 'newJob';
		}
	}

	/**
	 * Start a new replacement job. Displays the interface to set up the replacements.
	 *
	 * @return  void
	 */
	public function newJob()
	{
		/** @var ReplaceModel $model */
		$model = $this->model;

		$reset = $this->input->getBool('reset', false);

		// Assign the Configuration object to the View object
		/** @var Html $view */
		$view                = $this->view;
		$view->configuration = $model->getCachedConfiguration($reset);

		// Display the HTML page
		$this->display();
	}

	/**
	 * Returns a list of all database tables.
	 *
	 * @return  void
	 */
	public function getTablesHTML()
	{
		@ob_clean();

		if (!$this->csrfProtection('getTablesHTML', false))
		{
			header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden");

			exit();
		}

		/** @var ReplaceModel $model */
		$model     = $this->model;
		$allTables = $this->input->getBool('allTables', false);
		$tables    = $model->getDatabaseTables($allTables);

		echo '###' . json_encode($tables) . '###';

		exit();
	}

	/**
	 * Shows the replacement progress interface
	 *
	 * @return  void
	 */
	public function replace()
	{
		if (!$this->csrfProtection('replace', true, 'get'))
		{
			throw new \RuntimeException(__('Access denied', 'akeebareplace'), 403);
		}

		/**
		 * If this is a POST request we need to apply and cache the new configuration.
		 */
		if ($this->input->getMethod() == 'POST')
		{
			$this->applyNewConfiguration();
		}

		// Set up the view
		/** @var Html $view */
		$view                = $this->view;
		/** @var \Akeeba\Replace\WordPress\Model\Replace $model */
		$model               = $this->model;
		$view->configuration = $model->getCachedConfiguration();

		$this->display();
	}

	/**
	 * Start a replacement job. Resets the replacement engine and triggers a step.
	 */
	public function start()
	{
		if (!$this->csrfProtection('replace', true, 'get'))
		{
			throw new \RuntimeException(__('Access denied', 'akeebareplace'), 403);
		}

		/** @var \Akeeba\Replace\WordPress\Model\Replace $model */
		$model  = $this->model;

		// Create a new engine
		/** @var Database $engine */
		$engine = $model->makeEngine($model->getCachedConfiguration());
		$model->setEngineCache($engine);

		// Run the first engine step
		$this->step();
	}

	/**
	 * Process the next replacement step.
	 */
	public function step()
	{
		if (!$this->csrfProtection('replace', true, 'get'))
		{
			throw new \RuntimeException(__('Access denied', 'akeebareplace'), 403);
		}

		// Get the engine
		/** @var \Akeeba\Replace\WordPress\Model\Replace $model */
		$model  = $this->model;
		$engine = $model->getEngine();

		// Prime the status with an error if we cannot find an engine
		$status = new PartStatus([
			'Error' => 'Trying to step the replacement engine after it has finished processing replacements.'
		]);

		// Run a single step if we really do have an engine
		if (!is_null($engine))
		{
			$status = $engine->tick();
		}

		// If we are done (or died with an error) we set the engine to null; this will unset it from the cache.
		if ($status->isDone() || ($status->getError() !== ''))
		{
			$engine = null;
		}

		// Cache the new engine status
		$model->setEngineCache($engine);

		// Send the output to the browser
		@ob_end_clean();

		echo '###' . json_encode($status->toArray()) . '###';

		exit();
	}

	/**
	 * Convert the input data to a Configuration object and cache it
	 */
	protected function applyNewConfiguration()
	{
		/** @var ReplaceModel $model */
		$model         = $this->model;
		$defaultConfig = $model->makeConfiguration()->toArray();

		/**
		 * Process POST data
		 *
		 * A few words about checkboxes. Browsers only submit the checkboxes which are checked. The unchecked boxes are
		 * NOT submitted. The value of the submitted checkboxes is _usually_ on except if you have a value attribute
		 * which we don't. So, we can't use getBool because the input is not boolean and not guaranteed to be there.
		 * Instead we try to fetch as a filtered string (cmd) with the default value "borg". If a checkbox was unchecked
		 * the value we fetch will be "borg". Therefore "borg" == false and anything else == true. Don't you LOVE how
		 * the web is cardboard held together by string and duct tape?
		 */
		$from               = $this->input->post->get('replace_from', '', 'raw');
		$to                 = $this->input->post->get('replace_to', '', 'raw');
		$hasOutput          = $this->input->post->getCmd('exportAsSQL', 'borg') != 'borg';
		$hasBackup          = $this->input->post->getCmd('takeBackups', 'borg') != 'borg';
		$logLevel           = $this->input->post->getInt('akeebareplaceLogLevel', 10);
		$liveMode           = $this->input->post->getCmd('liveMode', 'borg') != 'borg';
		$allTables          = $this->input->post->getCmd('allTables', 'borg') != 'borg';
		$regularExpressions = $this->input->post->getCmd('regularExpressions', 'borg') != 'borg';
		$maxBatchSize       = $this->input->post->getInt('batchSize', 1000);
		$excludeTables      = $this->input->post->get('excludeTables', [], 'array');
		$rawExcludeColumns  = $this->input->post->get('excludeRows', '', 'raw');
		$databaseCollation  = $this->input->post->getCmd('databaseCollation', '');
		$tableCollation     = $this->input->post->getCmd('tableCollation', '');
		$hasLog             = true;

		/**
		 * Convert from and to into proper arrays. Yes, browsers send \r\n even when running under Linux. No, we should
		 * not array_map with trim because you might actually want to replace "foo " with "foo_" (but NOT "foo" with
		 * "foo_") so it wouldn't really help you any if we trimmed the values.
		 */
		$from = explode("\r\n", $from);
		$to   = explode("\r\n", $to);

		// Filter table exclusions, removing empty and duplicate values
		$excludeTables = array_map('trim', $excludeTables);
		$excludeTables = array_filter($excludeTables, function ($v) {
			return !empty($v);
		});
		$excludeTables = array_unique($excludeTables);

		// Convert excluded columns from table.column format to a usable array
		$excludeColumns    = [];
		$rawExcludeColumns = str_replace(',', "\n", $rawExcludeColumns);
		$rawExcludeColumns = explode("\n", $rawExcludeColumns);
		array_walk($rawExcludeColumns, function ($v) use (&$excludeColumns) {
			$v = trim($v);

			if (empty($v))
			{
				return;
			}

			if (strpos($v, '.') === false)
			{
				return;
			}

			list($table, $column) = explode('.', $v, 2);

			if (!array_key_exists($table, $excludeColumns))
			{
				$excludeColumns[$table] = [];
			}

			$excludeColumns[$table][] = $column;
		});

		// Convert our fake error level "none" to values suitable for no logging.
		if ($logLevel > LoggerInterface::SEVERITY_ERROR)
		{
			$logLevel = LoggerInterface::SEVERITY_ERROR;
			$hasLog   = false;
		}

		// Create and save the engine configuration
		$newConfig = [
			'outputSQLFile'      => $hasOutput ? $defaultConfig['outputSQLFile'] : '',
			'backupSQLFile'      => $hasBackup ? $defaultConfig['backupSQLFile'] : '',
			'logFile'            => $hasLog ? $defaultConfig['logFile'] : '',
			'minLogLevel'        => $logLevel,
			'liveMode'           => $liveMode,
			'allTables'          => $allTables,
			'maxBatchSize'       => $maxBatchSize,
			'excludeTables'      => $excludeTables,
			'excludeRows'        => $excludeColumns,
			'regularExpressions' => $regularExpressions,
			'replacements'       => array_combine($from, $to),
			'databaseCollation'  => $databaseCollation,
			'tableCollation'     => $tableCollation,
		];

		$configuration = new Configuration($newConfig);

		$model->setCachedConfiguration($configuration);
	}
}