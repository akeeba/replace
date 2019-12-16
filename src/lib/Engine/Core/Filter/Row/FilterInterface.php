<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Replace\Engine\Core\Filter\Row;

use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Logger\LoggerInterface;

/**
 * Interface to a row list filter
 *
 * @package Akeeba\Replace\Engine\Core\Filter\Row
 */
interface FilterInterface
{
	/**
	 * FilterInterface  constructor.
	 *
	 * @param   LoggerInterface  $logger   The logger used to log our actions
	 * @param   Driver           $db       The database connection object
	 * @param   Configuration    $config   The engine configuration
	 */
	public function __construct(LoggerInterface $logger, Driver $db, Configuration $config);

	/**
	 * Check whether the table row should be processed or not
	 *
	 * @param   $tableName  string  The name of the table being processed
	 * @param   $row        array   The row being processed
	 *
	 * @return  bool  True to allow processing
	 */
	public function filter($tableName, array $row);
}