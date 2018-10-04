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

		// Assign the Configuration object to the View object
		/** @var Html $view */
		$view                = $this->view;
		$view->configuration = $model->getCachedConfiguration();

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

		/** @var ReplaceModel $model */
		$model         = $this->model;
		$defaultConfig = $model->makeConfiguration()->toArray();

		// Process POST data
		$from               = $this->input->post->get('replace_from', [], 'array');
		$to                 = $this->input->post->get('replace_to', [], 'array');
		$hasOutput          = $this->input->post->getBool('exportAsSQL', true);
		$hasBackup          = $this->input->post->getBool('takeBackups', true);
		$logLevel           = $this->input->post->getInt('akeebareplaceLogLevel', 10);
		$liveMode           = $this->input->post->getBool('liveMode', true);
		$allTables          = $this->input->post->getBool('allTables', false);
		$regularExpressions = $this->input->post->getBool('regularExpressions', false);
		$maxBatchSize       = $this->input->post->getInt('batchSize', 1000);
		$excludeTables      = $this->input->post->get('excludeTables', [], 'array');
		$rawExcludeColumns  = $this->input->post->get('excludeRows', '', 'raw');
		$databaseCollation  = $this->input->post->getCmd('databaseCollation', '');
		$tableCollation     = $this->input->post->getCmd('tableCollation', '');
		$hasLog             = true;

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

		// Set up the view
		/** @var Html $view */
		$view = $this->view;
		$view->configuration = $configuration;

		$this->display();
	}
}