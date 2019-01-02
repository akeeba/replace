<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core\Action\Table;

use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Database\Metadata\Column;
use Akeeba\Replace\Database\Metadata\Table as TableMeta;
use Akeeba\Replace\Engine\Core\Action\ActionAwareInterface;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAwareInterface;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Writer\WriterInterface;

trait ActionAware
{
	/**
	 * @param   array            $perTableActionClasses  The list of per table action classes to use
	 * @param   TableMeta        $tableMeta              The metadata of the table to process
	 * @param   Column[]         $columns                The column metadata of the table to process
	 * @param   LoggerInterface  $logger                 The logger to use
	 * @param   WriterInterface  $backupWriter           The backup writer to use
	 * @param   WriterInterface  $outputWriter           The output writer to use
	 * @param   Driver           $db                     The database to execute SQL against
	 * @param   Configuration    $config                 The Configuration object to use
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	protected function runPerTableActions(array $perTableActionClasses,
	                                      TableMeta $tableMeta, array $columns,
	                                      LoggerInterface $logger, WriterInterface $outputWriter,
	                                      WriterInterface $backupWriter, Driver $db, Configuration $config)
	{
		if (empty($perTableActionClasses))
		{
			$logger->info("No actions to be performed on the table itself.");

			return;
		}

		$logger->info("Processing actions to be performed on the table itself.");

		$liveMode        = $config->isLiveMode();
		$numActions      = 0;
		$hasOutputWriter = $outputWriter->getFilePath() != '';

		foreach ($perTableActionClasses as $class)
		{
			$numActions += $this->runPerTableAction($class, $tableMeta, $columns, $logger, $backupWriter, $outputWriter,
				$db, $config);
		}

		$this->logNumberOfActions($logger, $tableMeta->getName(), $liveMode, $hasOutputWriter, $numActions);
	}

	/**
	 * Runs a table action given an action class name and returns the number of action queries generated
	 *
	 * @param   string           $class         The action class to create an object from
	 * @param   TableMeta        $tableMeta     The metadata of the table to process
	 * @param   Column[]         $columns       The column metadata of the table to process
	 * @param   LoggerInterface  $logger        The logger to use
	 * @param   WriterInterface  $backupWriter  The backup writer to use
	 * @param   WriterInterface  $outputWriter  The output writer to use
	 * @param   Driver           $db            The database to execute SQL against
	 * @param   Configuration    $config        The Configuration object to use
	 *
	 * @return  int
	 */
	protected function runPerTableAction($class, TableMeta $tableMeta, array $columns, LoggerInterface $logger,
	                                     WriterInterface $backupWriter, WriterInterface $outputWriter, Driver $db,
	                                     Configuration $config)
	{
		if (!class_exists($class))
		{
			if ($this instanceof WarningsAwareInterface)
			{
				$this->addWarningMessage(sprintf("Action class “%s” does not exist", $class));
			}

			return 0;
		}

		if (!in_array('Akeeba\Replace\Engine\Core\Action\Table\ActionInterface', class_implements($class)))
		{
			if ($this instanceof WarningsAwareInterface)
			{
				$this->addWarningMessage(sprintf("Action class “%s” is not a valid per-table action", $class));
			}

			return 0;
		}

		$classParts = explode('\\', $class);
		$baseClass  = array_pop($classParts);

		$logger->debug(sprintf("Running “%s” action class against table “%s”.", $baseClass, $tableMeta->getName()));


		/** @var ActionInterface $o */
		$o        = new $class($db, $logger, $config);
		$response = $o->processTable($tableMeta, $columns);

		if ($this instanceof ActionAwareInterface)
		{
			$this->applyBackupQueries($response, $backupWriter);

			return $this->applyActionQueries($response, $outputWriter, $db, $config->isLiveMode(), false);
		}

		return 0;
	}

	/**
	 * Log the results of per-table actions
	 *
	 * @param   LoggerInterface  $logger           The logger to output to
	 * @param   string           $tableName        The name of the table we processed
	 * @param   bool             $liveMode         Was this Live Mode (ran against the real database)?
	 * @param   bool             $hasOutputWriter  Did we have an output writer to begin with?
	 * @param   int              $numActions       How many actions did we take?
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	protected function logNumberOfActions(LoggerInterface $logger, $tableName, $liveMode, $hasOutputWriter, $numActions)
	{
		// Live Mode -- message indicates we did something
		$message = "Actions performed on the table %s: %d";

		if (!$liveMode)
		{
			$logger->info(sprintf($message, $tableName, $numActions));

			return;
		}

		// Dry Run with Save To File -- message indicates we wrote something to a file
		$message = "Actions to be performed on the table %s (saved in SQL file): %d";

		// Dry Run without Save To File -- message indicates we did not execute anything
		if (!$hasOutputWriter)
		{
			$message = "Actions which would have been performed on the table %s: %d";
		}

		$logger->info(sprintf($message, $tableName, $numActions));
	}

}