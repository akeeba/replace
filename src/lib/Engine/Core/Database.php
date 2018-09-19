<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Core;

use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Database\Query;
use Akeeba\Replace\Engine\AbstractPart;
use Akeeba\Replace\Engine\Core\Table as TablePart;
use Akeeba\Replace\Logger\LoggerAware;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Timer\TimerInterface;
use Akeeba\Replace\Writer\FileWriter;
use Akeeba\Replace\Writer\WriterInterface;

/**
 * An Engine Part which iterates a database for tables
 *
 * @package Akeeba\Replace\Engine\Part
 */
class Database extends AbstractPart
{
	use LoggerAware;

	/**
	 * The driver we are using to connect to our database.
	 *
	 * @var  Driver
	 */
	protected $db = null;

	/**
	 * The writer to use for action SQL file output
	 *
	 * @var  WriterInterface
	 */
	private $outputWriter;

	/**
	 * The writer to use for backup SQL file output
	 *
	 * @var  WriterInterface
	 */
	private $backupWriter;

	/**
	 * The list of tables to process. Initialized in prepare().
	 *
	 * @var  array
	 */
	private $tableList = [];

	/**
	 * The Engine Part we tick to process a table
	 *
	 * @var  AbstractPart
	 */
	private $tablePart = null;

	/**
	 * Overloaded constructor.
	 *
	 * @param TimerInterface  $timer
	 * @param Driver          $db
	 * @param LoggerInterface $logger
	 * @param Configuration   $config
	 */
	public function __construct(TimerInterface $timer, Driver $db, LoggerInterface $logger, Configuration $config)
	{
		$this->db = $db;

		$this->setLogger($logger);

		parent::__construct($timer, $config);
	}

	/**
	 * Executes when the state is STATE_INIT. You are supposed to set up internal objects and do any other kind of
	 * preparatory work which does not take too much time.
	 *
	 * @return  void
	 */
	protected function prepare()
	{
		// Set up the writers
		$this->setupOutputWriter();
		$this->setupBackupWriter();

		// Log the Live Mode status
		$this->logLiveModeStatus();

		// Log a message about backups (only in Live Mode)
		$this->logMessageAboutBackups();

		// Run once-per-database callbacks.
		$this->runPerDatabaseActions();

		// Get and filter the list of tables.
		$this->getLogger()->debug('Getting the list of database tables');
		$this->tableList = $this->db->getTableList();
		$this->tableList = $this->filterNonCoreTables($this->tableList);
		$this->tableList = $this->filterTables($this->tableList);
	}

	/**
	 * Main processing. Here you do the bulk of the work. When you no longer have any more work to do return boolean
	 * false.
	 *
	 * @return  bool  false to indicate you are done, true to indicate more work is to be done.
	 */
	protected function process()
	{
		// If no current table is set we need to iterate the next table
		if (empty($this->tablePart))
		{
			try
			{
				$this->takeNextTable();
			}
			catch (\UnderflowException $e)
			{
				// Oh, no more tables on the list. We are done here.
				return false;
			}

			// The table was filtered out, e.g. because it's a VIEW, not a table. Get the next table on the next tick.
			if (empty($this->tablePart))
			{
				return true;
			}
		}

		// I'm running out of time. Let processing take place in the next step.
		if ($this->timer->getTimeLeft() < 0.001)
		{
			return true;
		}

		// Run a single step of the table processing Engine Part
		$status = $this->tablePart->tick();

		// Inherit warnings and errors
		$this->inheritWarningsFrom($this->tablePart);
		$this->inheritErrorFrom($this->tablePart);

		// If we have an error we must stop processing right away
		if (is_object($status->getError()))
		{
			return false;
		}

		// If the table processing Engine Part is done we indicate we need a new table
		if ($status->isDone())
		{
			$this->tablePart = null;
		}

		// We have more work to do
		return true;
	}

	protected function finalize()
	{
		// Close possibly open files by destroying the writers
		$this->outputWriter = null;
		$this->backupWriter = null;
	}

	/**
	 * Setup a file writer for the output SQL file if necessary.
	 */
	protected function setupOutputWriter()
	{
		$outputSQLFile = $this->config->getOutputSQLFile();

		if (empty($outputSQLFile))
		{
			$this->getLogger()->info("Output SQL file: (none)");

			return;
		}

		$this->getLogger()->info("Output SQL file: $outputSQLFile");

		$this->outputWriter = new FileWriter($outputSQLFile, true);
	}

	/**
	 * Setup a file writer for the backup SQL file if necessary.
	 */
	protected function setupBackupWriter()
	{
		$backupSQLFile = $this->config->getBackupSQLFile();

		if (empty($backupSQLFile))
		{
			$this->getLogger()->info("Backup SQL file: (none)");

			return;
		}

		$this->getLogger()->info("Backup SQL file: $backupSQLFile");

		$this->backupWriter = new FileWriter($backupSQLFile, true);
	}

	protected function runPerDatabaseActions()
	{
		// Get the action classes to run
		$perDatabaseActionClasses = $this->config->getPerDatabaseClasses();
		$liveMode                 = $this->config->isLiveMode();

		if (empty($perDatabaseActionClasses))
		{
			$this->getLogger()->info("No actions to be performed on the database itself.");

			return;
		}

		$this->getLogger()->info("Processing actions to be performed on the database itself.");

		$this->getLogger()->debug("Retrieving database metadata");
		$databaseMeta = $this->db->getDatabaseMeta();

		$numActions   = 0;

		foreach ($perDatabaseActionClasses as $class)
		{
			if (!in_array(__NAMESPACE__ . '\\DatabaseActionInterface', class_implements($class)))
			{
				$this->addWarningMessage(sprintf("Action class “%s” is not a valid per-database action", $class));

				continue;
			}

			$this->getLogger()->debug(sprintf("Running “%s” action class against database.", $class));

			/** @var DatabaseActionInterface $o */
			$o            = new $class($this->db, $this->getLogger());
			$response     = $o->processDatabase($databaseMeta);
			$outputWriter = $this->outputWriter;
			$backupWriter = $this->backupWriter;
			$db           = $this->db;

			if ($response->hasRestorationQueries() && !is_null($backupWriter))
			{
				array_map(function (Query $query) use ($backupWriter) {
					$this->getLogger()->debug("Backup SQL: " . $query);
					$backupWriter->writeLine($query);
				}, $response->getRestorationQueries());
			}

			if ($response->hasActionQueries())
			{
				array_map(function (Query $query) use ($db, $outputWriter, $liveMode, &$numActions) {
					$numActions++;

					if (!is_null($outputWriter))
					{
						$this->getLogger()->debug("Output SQL: " . $query);
						$outputWriter->writeLine($query);
					}

					if ($liveMode)
					{
						try
						{
							$this->getLogger()->debug("Execute SQL: " . $query);
							$db->setQuery($query)->execute();
						}
						catch (\RuntimeException $e)
						{
							$this->addWarningMessage("Taking per-database action failed. SQL command: " . $query);
						}
					}
				}, $response->getActionQueries());
			}
		}

		// Live Mode -- message indicates we did something
		$message = "Actions performed on the database itself: %d";

		if (!$liveMode)
		{
			// Dry Run with Save To File -- message indicates we wrote something to a file
			$message = "Actions to be performed on the database itself (saved in SQL file): %d";

			// Dry Run without Save To File -- message indicates we did not execute anything
			if (!is_object($outputWriter))
			{
				$message = "Actions which would have been performed on the database itself: %d";
			}
		}

		$this->getLogger()->info(sprintf($message, $numActions));
	}

	/**
	 * Log the Live Mode status. This tells the user what will and will not happen as a result of their actions.
	 *
	 * @return  void
	 */
	protected function logLiveModeStatus()
	{
		$message = "Live Mode: Enabled. Your database WILL be modified.";

		if (!$this->config->isLiveMode())
		{
			$message = "Live Mode: Disabled. Your database will NOT be modified.";

			if (is_object($this->outputWriter))
			{
				$message .= ' The actions to be taken will be saved in the Output SQL file instead.';
			}
		}

		$this->getLogger()->info($message);
	}

	/**
	 * Logs a message about backups. Only for Live Mode.
	 */
	protected function logMessageAboutBackups()
	{
		if (!$this->config->isLiveMode())
		{
			return;
		}

		if (is_object($this->backupWriter))
		{
			$this->getLogger()->info("If your site breaks after running Akeeba Replace please execute the Backup SQL file to restore it back to its previous state. If you're not sure how -- please read the documentation or ask us.");

			return;
		}

		$this->addWarningMessage('YOU ARE RUNNING Akeeba Replace WITHOUT TAKING BACKUPS. IF YOUR SITE BREAKS WE WILL NOT BE ABLE TO HELP YOU.');
	}

	/**
	 * Filter out the tables which do not start with the configured prefix. If the configuration parameter allTables
	 * is set this filter does nothing.
	 *
	 * @param   array  $tables  A list of tables to filter
	 *
	 * @return  array  The filtered tables
	 */
	protected function filterNonCoreTables($tables)
	{
		if (!$this->config->isAllTables())
		{
			$this->getLogger()->debug("Non-core table filters will NOT be taken into account: allTables is true.");

			return $tables;
		}

		$prefix = $this->db->getPrefix();
		$pLen   = strlen($prefix);

		$this->getLogger()->debug("Applying table filter: non-core");

		return array_filter($tables, function ($tableName) use ($prefix, $pLen) {
			if (strlen($tableName) < ($pLen + 1))
			{
				return false;
			}

			if (substr($tableName, 0, $pLen) != $prefix)
			{
				$this->getLogger()->debug("Skipping table $tableName");

				return false;
			}

			return true;
		});
	}

	/**
	 * Filter out the tables based on user-defined criteria
	 *
	 * @param   array  $tables  A list of tables to filter
	 *
	 * @return  array  The filtered tables
	 */
	protected function filterTables($tables)
	{
		$tableFilters = $this->config->getExcludeTables();

		if (empty($tableFilters))
		{
			$this->getLogger()->debug("Table filters will NOT be taken into account: no table filters have been defined.");

			return $tables;
		}

		// Convert table filters from abstract to concrete names. Lets you use filters like '#__foo' instead of 'wp_foo'
		$db           = $this->db;
		$tableFilters = array_map(function ($v) use ($db) {
			return $db->replacePrefix($v);
		}, $tableFilters);

		$this->getLogger()->debug("Applying table filter: excluded tables");

		return array_filter($tables, function ($tableName) use ($tableFilters) {
			if (in_array($tableName, $tableFilters))
			{
				$this->getLogger()->debug("Skipping table $tableName");

				return false;
			}

			return true;
		});
	}

	/**
	 * Prepare to operate on the next table on the list.
	 */
	protected function takeNextTable()
	{
		// Make sure there are more tables to process
		if (empty($this->tableList))
		{
			throw new \UnderflowException("The list of tables is empty");
		}

		// Get the table meta of the next table to process
		$tableName       = array_shift($this->tableList);
		$tableMeta       = $this->db->getTableMeta($tableName);
		$this->tablePart = null;

		if (is_null($tableMeta->getEngine()))
		{
			// This is a VIEW, not a table. I cannot replace data in a view.
			$this->getLogger()->debug(sprintf('Skipping table %s (this is a VIEW, not a table)', $tableName));

			$this->tablePart = null;

			return;
		}

		// Create a new table Engine Part
		$this->tablePart = new TablePart($this->timer, $this->db, $this->getLogger(), $this->config, $this->outputWriter, $this->backupWriter, $tableMeta);

	}
}