<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core\Action\Table;


use Akeeba\Replace\Database\Metadata\Column;
use Akeeba\Replace\Database\Metadata\Table;
use Akeeba\Replace\Engine\Core\Response\SQL;

class Collation extends AbstractAction
{
	/**
	 * Take a table connection and figure out if we need to run table-level DDL queries.
	 *
	 * @param   Table     $table    The metadata of the table to be processed
	 * @param   Column[]  $columns  The metadata of the table columns
	 *
	 * @return  SQL
	 */
	public function processTable(Table $table, array $columns)
	{
		$newCollation     = $this->getConfig()->getTableCollation();
		$currentCollation = $table->getCollation();

		if (empty($newCollation) || ($currentCollation == $newCollation))
		{
			return new SQL([], []);
		}

		$collationParts = explode('_', $newCollation);
		$newCharset     = $collationParts[0];
		$oldColParts    = explode('_', $currentCollation);
		$oldCharset     = $oldColParts[0];
		$driver         = $this->getDbo();
		$queryTemplate  = 'ALTER TABLE %s CONVERT TO CHARACTER SET %s COLLATE %s';
		$backupQuery    = sprintf($queryTemplate, $driver->qn($table->getName()), $oldCharset, $currentCollation);
		$actionQuery    = sprintf($queryTemplate, $driver->qn($table->getName()), $newCharset, $newCollation);

		/**
		 * Why not write code to change each column? Well, the CONVERT TO syntax converts both the table and all of its
		 * text columns. The "gotcha" is only if we have a non-UTF column storing UTF data which will be corrupt upon
		 * conversion. No way to do this automatically. You need to *know* that this is the case and convert the column
		 * first to BLOB, then to a UTF8 columns.
		 *
		 * @see https://dev.mysql.com/doc/refman/5.6/en/alter-table.html
		 */
		return new SQL([$actionQuery], [$backupQuery]);
	}
}