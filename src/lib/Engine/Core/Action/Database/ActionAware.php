<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core\Action\Database;

use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Database\Metadata\Database as DatabaseMeta;
use Akeeba\Replace\Engine\Core\Action\ActionAwareInterface;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAwareInterface;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Writer\WriterInterface;

/**
 * A trait for classes implementing per database actions
 *
 * @package Akeeba\Replace\Engine\Core\Action\Database
 */
trait ActionAware
{
	/**
	 * Execute per-database actions
	 *
	 * @param   array            $perDatabaseActionClasses  A list of per database action classes to use
	 * @param   DatabaseMeta     $databaseMeta              The metadata of the DB to process
	 * @param   LoggerInterface  $logger                    The logger to use
	 * @param   WriterInterface  $backupWriter              The backup writer to use
	 * @param   WriterInterface  $outputWriter              The output writer to use
	 * @param   Driver           $db                        The database to execute SQL against
	 * @param   Configuration    $config                    The Configuration object to use
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	protected function runPerDatabaseActions(array $perDatabaseActionClasses, DatabaseMeta $databaseMeta,
	                                         LoggerInterface $logger, WriterInterface $outputWriter,
	                                         WriterInterface $backupWriter, Driver $db, Configuration $config)
	{

		if (empty($perDatabaseActionClasses))
		{
			$logger->info("No actions to be performed on the database itself.");

			return;
		}

		$logger->info("Processing actions to be performed on the database itself.");

		$liveMode        = $config->isLiveMode();
		$numActions      = 0;
		$hasOutputWriter = $outputWriter->getFilePath() != '';

		foreach ($perDatabaseActionClasses as $class)
		{
			$numActions += $this->runPerDatabaseAction($class, $databaseMeta, $logger, $backupWriter, $outputWriter,
				$db, $config);
		}

		$this->logNumberOfActions($logger, $liveMode, $hasOutputWriter, $numActions);
	}

	/**
	 * Runs a database action given an action class name and returns the number of action queries generated
	 *
	 * @param   string           $class         The action class to create an object from
	 * @param   DatabaseMeta     $databaseMeta  The metadata of the DB to process
	 * @param   LoggerInterface  $logger        The logger to use
	 * @param   WriterInterface  $backupWriter  The backup writer to use
	 * @param   WriterInterface  $outputWriter  The output writer to use
	 * @param   Driver           $db            The database to execute SQL against
	 * @param   Configuration    $config        The Configuration object to use
	 *
	 * @return  int
	 */
	protected function runPerDatabaseAction($class, DatabaseMeta $databaseMeta, LoggerInterface $logger,
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

		if (!in_array('Akeeba\Replace\Engine\Core\Action\Database\ActionInterface', class_implements($class)))
		{
			if ($this instanceof WarningsAwareInterface)
			{
				$this->addWarningMessage(sprintf("Action class “%s” is not a valid per-database action", $class));
			}

			return 0;
		}

		$classParts = explode('\\', $class);
		$baseClass  = array_pop($classParts);

		$logger->debug(sprintf("Running “%s” action class against database.", $baseClass));


		/** @var ActionInterface $o */
		$o        = new $class($db, $logger, $config);
		$response = $o->processDatabase($databaseMeta);

		if ($this instanceof ActionAwareInterface)
		{
			$this->applyBackupQueries($response, $backupWriter);

			return $this->applyActionQueries($response, $outputWriter, $db, $config->isLiveMode(), false);
		}

		return 0;
	}

	/**
	 * Log the results of per-database actions
	 *
	 * @param   LoggerInterface  $logger           The logger to output to
	 * @param   bool             $liveMode         Was this Live Mode (ran against the real database)?
	 * @param   bool             $hasOutputWriter  Did we have an output writer to begin with?
	 * @param   int              $numActions       How many actions did we take?
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	protected function logNumberOfActions(LoggerInterface $logger, $liveMode, $hasOutputWriter, $numActions)
	{
		// Live Mode -- message indicates we did something
		$message = "Actions performed on the database itself: %d";

		if (!$liveMode)
		{
			$logger->info(sprintf($message, $numActions));

			return;
		}

		// Dry Run with Save To File -- message indicates we wrote something to a file
		$message = "Actions to be performed on the database itself (saved in SQL file): %d";

		// Dry Run without Save To File -- message indicates we did not execute anything
		if (!$hasOutputWriter)
		{
			$message = "Actions which would have been performed on the database itself: %d";
		}

		$logger->info(sprintf($message, $numActions));
	}

}