<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Core;

use Akeeba\Replace\Database\DatabaseAware;
use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Database\Query;
use Akeeba\Replace\Engine\AbstractPart;
use Akeeba\Replace\Engine\Core\Filter\Table\FilterInterface;
use Akeeba\Replace\Engine\Core\Helper\MemoryInfo;
use Akeeba\Replace\Engine\Core\Table as TablePart;
use Akeeba\Replace\Logger\LoggerAware;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Timer\TimerInterface;
use Akeeba\Replace\Writer\WriterInterface;

/**
 * An Engine Part which iterates a database for tables
 *
 * @package Akeeba\Replace\Engine\Part
 */
class Database extends AbstractPart
{
	use LoggerAware;
	use DatabaseAware;
	use ConfigurationAware;

	/**
	 * Hard-coded list of table filter classes. This is for my convenience.
	 *
	 * @var  array
	 */
	private $filters = [
		'Akeeba\\Replace\\Engine\\Core\\Filter\\Table\\NonCore',
		'Akeeba\\Replace\\Engine\\Core\\Filter\\Table\\UserFilters',
	];

	/**
	 * The memory information helper, used to take decisions based on the available PHP memory
	 *
	 * @var  MemoryInfo
	 */
	protected $memoryInfo = null;

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
	 * @param   TimerInterface   $timer         Timer object
	 * @param   Driver           $db            Database driver object
	 * @param   LoggerInterface  $logger        Logger object
	 * @param   WriterInterface  $outputWriter  Output SQL file writer (null to disable the feature)
	 * @param   WriterInterface  $backupWriter  Backup SQL file writer (null to disable the feature)
	 * @param   Configuration    $config        Engine configuration
	 * @param   MemoryInfo       $memoryInfo    Memory information helper object
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct(TimerInterface $timer, Driver $db, LoggerInterface $logger, WriterInterface $outputWriter, WriterInterface $backupWriter, Configuration $config, MemoryInfo $memoryInfo)
	{
		$this->setDriver($db);
		$this->setLogger($logger);
		$this->setConfig($config);

		$this->memoryInfo   = $memoryInfo;
		$this->outputWriter = $outputWriter;
		$this->backupWriter = $backupWriter;

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
		// Log things the user should know
		$this->getLogger()->info(sprintf("Starting to process replacements in database “%s”", $this->getDbo()->getDatabase()));

		$this->logOutputWriter();
		$this->logBackupWriter();
		$this->logLiveModeStatus();
		$this->logMessageAboutBackups();

		// Run once-per-database callbacks.
		$this->runPerDatabaseActions();

		// Get and filter the list of tables.
		$this->getLogger()->debug('Getting the list of database tables');
		$this->tableList = $this->getDbo()->getTableList();

		$this->getLogger()->debug('Filtering the list of database tables');
		$this->tableList = $this->applyFilters($this->tableList, $this->filters);
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

	/**
	 * Finalization. Here you are supposed to perform any kind of tear down after your work is done.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	protected function finalize()
	{
		$this->getLogger()->info(sprintf("Finished processing replacements in database “%s”", $this->getDbo()->getDatabase()));
	}

	/**
	 * Apply the hard-coded list of table filters against the provided table list
	 *
	 * @param   array  $tables   The tables to filters
	 * @param   array  $filters  List of filter classes to instantiate
	 *
	 * @return  array  The filtered tables after applying all filters
	 */
	private function applyFilters(array $tables, array $filters)
	{
		foreach ($filters as $class)
		{
			if (!class_exists($class))
			{
				$this->addWarningMessage(sprintf("Filter class “%s” not found. Is your installation broken?", $class));

				continue;
			}

			if (!in_array('Akeeba\\Replace\\Engine\\Core\\Filter\\Table\\FilterInterface', class_implements($class)))
			{
				$this->addWarningMessage(sprintf("Filter class “%s” is not a valid table filter. Is your installation broken?", $class));

				continue;
			}

			/** @var FilterInterface $o */
			$o = new $class($this->getLogger(), $this->getDomain(), $this->getConfig());
			$tables = $o->filter($tables);
		}

		return $tables;
	}

	/**
	 * Log the path (if any) of the output SQL file
	 *
	 * @return  void
	 */
	protected function logOutputWriter()
	{
		$path = $this->outputWriter->getFilePath();

		if (empty($path))
		{
			$path = '(none)';
		}

		$this->getLogger()->info("Output SQL file: $path");
	}

	/**
	 * Log the path (if any) of the backup SQL file
	 *
	 * @return  void
	 */
	protected function logBackupWriter()
	{
		$path = $this->backupWriter->getFilePath();

		if (empty($path))
		{
			$path = '(none)';
		}

		$this->getLogger()->info("Backup SQL file: $path");
	}

	/**
	 * Log the Live Mode status. This tells the user what will and will not happen as a result of their actions.
	 *
	 * @return  void
	 */
	protected function logLiveModeStatus()
	{
		$message = "Live Mode: Enabled. Your database WILL be modified.";

		if (!$this->getConfig()->isLiveMode())
		{
			$message = "Live Mode: Disabled. Your database will NOT be modified.";

			if ($this->outputWriter->getFilePath())
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
		if (!$this->getConfig()->isLiveMode())
		{
			return;
		}

		if ($this->backupWriter->getFilePath())
		{
			$this->getLogger()->info("If your site breaks after running Akeeba Replace please execute the Backup SQL file to restore it back to its previous state. If you're not sure how -- please read the documentation or ask us.");

			return;
		}

		$this->addWarningMessage('YOU ARE RUNNING Akeeba Replace WITHOUT TAKING BACKUPS. IF YOUR SITE BREAKS WE WILL NOT BE ABLE TO HELP YOU.');
	}

	/**
	 * Execute the configured per-database actions
	 *
	 * @return  void
	 */
	protected function runPerDatabaseActions()
	{
		// Get the action classes to run
		$perDatabaseActionClasses = $this->getConfig()->getPerDatabaseClasses();
		$liveMode                 = $this->getConfig()->isLiveMode();

		if (empty($perDatabaseActionClasses))
		{
			$this->getLogger()->info("No actions to be performed on the database itself.");

			return;
		}

		$this->getLogger()->info("Processing actions to be performed on the database itself.");

		$this->getLogger()->debug("Retrieving database metadata");
		$databaseMeta = $this->getDbo()->getDatabaseMeta();

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
			$o            = new $class($this->getDbo(), $this->getLogger());
			$response     = $o->processDatabase($databaseMeta);
			$outputWriter = $this->outputWriter;
			$backupWriter = $this->backupWriter;
			$db           = $this->getDbo();

			if ($response->hasRestorationQueries())
			{
				array_map(function (Query $query) use ($backupWriter) {
					if ($backupWriter->getFilePath())
					{
						$this->getLogger()->debug("Backup SQL: " . $query);
					}

					$backupWriter->writeLine($query);
				}, $response->getRestorationQueries());
			}

			if ($response->hasActionQueries())
			{
				array_map(function (Query $query) use ($db, $outputWriter, $liveMode, &$numActions) {
					$numActions++;

					if ($outputWriter->getFilePath())
					{
						$this->getLogger()->debug("Output SQL: " . $query);
					}

					$outputWriter->writeLine($query);

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
			if ($outputWriter->getFilePath() == '')
			{
				$message = "Actions which would have been performed on the database itself: %d";
			}
		}

		$this->getLogger()->info(sprintf($message, $numActions));
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
		$tableMeta       = $this->getDbo()->getTableMeta($tableName);
		$this->tablePart = null;

		/**
		 * Filter out VIEWs -- Since VIEWs are stored SELECT queries they have no data of their own I need to replace.
		 *
		 * You might wonder why the heck do I not filter out views when I am applying all of the other table filters.
		 * It's for performance reasons. Database servers return tables and views names all together, with no indication
		 * of which one is what. Therefore I need to get the table/view metadata to determine if it's a table or a view.
		 * If you have a really big database with several hundred tables (think: multisites with dozens or hundreds of
		 * blogs in the network) this can be such a substantial amount of time that you end up with a timeout error.
		 *
		 * Since I am going to retrieve the table metadata upon beginning to process each table I have to do this query
		 * at this point in time anyway. Since it's one query, not hundreds, it takes very little time. And since this
		 * runs inside the context of a timer-aware Engine Step even if I run into hundreds of views back-to-back I will
		 * still NOT timeout: I can break the execution at any point when I determine I am running out of time and
		 * continue in the next step (page load).
		 */
		if (is_null($tableMeta->getEngine()))
		{
			// This is a VIEW, not a table. I cannot replace data in a view.
			$this->getLogger()->debug(sprintf('Skipping table %s (this is a VIEW, not a table)', $tableName));

			$this->tablePart = null;

			return;
		}

		// Create a new table Engine Part
		$this->tablePart = new TablePart($this->timer, $this->getDbo(), $this->getLogger(), $this->getConfig(), $this->outputWriter, $this->backupWriter, $tableMeta, $this->memoryInfo);
	}
}