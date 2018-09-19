<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Core;


use Akeeba\Replace\Database\Metadata\Database;
use Akeeba\Replace\Engine\Core\Response\SQL;

interface DatabaseActionInterface
{
	/**
	 * Take a database connection and figure out if we need to run database-level DDL queries.
	 *
	 * @param   Database $db The database definition we are processing
	 *
	 * @return  SQL
	 */
	public function processDatabase(Database $db);
}