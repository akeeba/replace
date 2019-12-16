<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Replace\Engine\Core\Action\Database;

use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Database\Metadata\Database;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Response\SQL;
use Akeeba\Replace\Logger\LoggerInterface;

/**
 * Interface to per-database action classes
 *
 * @package Akeeba\Replace\Engine\Core\Action\Database
 */
interface ActionInterface
{
	/**
	 * ActionInterface constructor.
	 *
	 * @param   Driver           $db      The database driver this action will be using
	 * @param   LoggerInterface  $logger  The logger this action will be using
	 * @param   Configuration    $config  The configuration for this object
	 */
	public function __construct(Driver $db, LoggerInterface $logger, Configuration $config);

	/**
	 * Take a database connection and figure out if we need to run database-level DDL queries.
	 *
	 * @param   Database  $db  The metadata of the database we are processing
	 *
	 * @return  SQL
	 */
	public function processDatabase(Database $db);
}