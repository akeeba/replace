<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Core\Action\Database;


use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Database\Metadata\Database;
use Akeeba\Replace\Engine\Core\Response\SQL;
use Akeeba\Replace\Logger\LoggerInterface;

interface ActionInterface
{
	/**
	 * ActionInterface constructor.
	 *
	 * @param   Driver           $db      The database driver this action will be using
	 * @param   LoggerInterface  $logger  The logger this action will be using
	 */
	public function __construct(Driver $db, LoggerInterface $logger);

	/**
	 * Take a database connection and figure out if we need to run database-level DDL queries.
	 *
	 * @param   Database $db The database definition we are processing
	 *
	 * @return  SQL
	 */
	public function processDatabase(Database $db);
}