<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core\Action;


use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Engine\Core\Response\SQL;
use Akeeba\Replace\Writer\WriterInterface;

interface ActionAwareInterface
{
	/**
	 * Apply the backup queries (save them to the WriterInterface object)
	 *
	 * @param   WriterInterface  $backupWriter  The backup writer to use
	 * @param   SQL              $response      The SQL response
	 *
	 * @return  void
	 */
	public function applyBackupQueries(SQL $response, WriterInterface $backupWriter);

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
	public function applyActionQueries(SQL $response, WriterInterface $outputWriter, Driver $db, $liveMode, $failOnError);
}