<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core\Action;


use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Database\Query;
use Akeeba\Replace\Engine\Core\Response\SQL;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAwareInterface;
use Akeeba\Replace\Logger\LoggerAwareInterface;
use Akeeba\Replace\Logger\NullLogger;
use Akeeba\Replace\Writer\WriterInterface;

/**
 * A trait which allows objects to process the responses of database and table action
 *
 * @package Akeeba\Replace\Engine\Core\Action
 */
trait ActionAware
{
	/**
	 * Apply the backup queries (save them to the WriterInterface object)
	 *
	 * @param   WriterInterface  $backupWriter  The backup writer to use
	 * @param   SQL              $response      The SQL response
	 *
	 * @return  void
	 */
	public function applyBackupQueries(SQL $response, WriterInterface $backupWriter)
	{
		if (!$response->hasRestorationQueries())
		{
			return;
		}

		$logger = new NullLogger();

		if ($this instanceof LoggerAwareInterface)
		{
			$logger = $this->getLogger();
		}

		array_map(function ($query) use ($backupWriter, $logger) {
			if ($backupWriter->getFilePath())
			{
				$logger->debug("Backup SQL: " . $query);
			}

			$backupWriter->writeLine(rtrim($query, ';') . ';');
		}, $response->getRestorationQueries());
	}

	/**
	 * Apply the action queries to the database and / or writing them to the WriterInterface object.
	 *
	 * @param   SQL              $response      The SQL response to process
	 * @param   WriterInterface  $outputWriter  The writer to use for outputting the queries
	 * @param   Driver           $db            The database driver for executing commands against the database
	 * @param   bool             $liveMode      True to execute queries against the database
	 * @param   bool             $failOnError   True to throw RuntimeExcpetion in case of a database error, false to set
	 *                                          a Warning instead.
	 *
	 * @return  int  Number of action queries processed
	 */
	public function applyActionQueries(SQL $response, WriterInterface $outputWriter, Driver $db, $liveMode, $failOnError)
	{
		$numActions = 0;

		if (!$response->hasActionQueries())
		{
			return $numActions;
		}

		$logger = new NullLogger();

		if ($this instanceof LoggerAwareInterface)
		{
			$logger = $this->getLogger();
		}

		array_map(function ($query) use ($db, $outputWriter, $liveMode, $failOnError, &$numActions, $logger) {
			$numActions++;

			if ($outputWriter->getFilePath())
			{
				$logger->debug("Output SQL: " . $query);
			}

			$outputWriter->writeLine(rtrim($query, ';') . ';');

			if (!$liveMode)
			{
				return;
			}

			$message = '';

			try
			{
				$logger->debug("Execute SQL: " . $query);
				$db->setQuery($query)->execute();

				return;
			}
			catch (\RuntimeException $e)
			{
				$message = sprintf(
					'Database error #%d with message “%s” when trying to run SQL command %s',
					$e->getCode(),
					$e->getMessage(),
					$query
				);
			}

			if (!$failOnError && ($this instanceof WarningsAwareInterface))
			{
				$this->addWarningMessage($message);

				return;
			}

			throw new \RuntimeException($message);
		}, $response->getActionQueries());

		return $numActions;
	}
}