<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Core;

use Akeeba\Replace\Engine\Database\Metadata\Table;
use Akeeba\Replace\Engine\Core\Response\SQL;

interface TableActionInterface
{
	/**
	 * Take a table connection and figure out if we need to run table-level DDL queries.
	 *
	 * @param   Table $table The definition of the table we are processing
	 *
	 * @return  SQL
	 */
	public function processTable(Table $table);
}