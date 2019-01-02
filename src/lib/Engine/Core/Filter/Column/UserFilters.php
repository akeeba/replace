<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core\Filter\Column;


use Akeeba\Replace\Database\Metadata\Column;
use Akeeba\Replace\Database\Metadata\Table;

class UserFilters extends AbstractFilter
{
	/**
	 * Filter the columns list, returning the filtered result
	 *
	 * @param   Table     $table    The table where the columns belong to
	 * @param   Column[]  $columns  The columns we are filtering
	 *
	 * @return  array
	 */
	public function filter(Table $table, array $columns)
	{
		// Make sure we have user filters, at all
		$allUserFilters = $this->getConfig()->getExcludeRows();

		if (empty($allUserFilters))
		{
			$this->getLogger()->debug("Table filters will NOT be taken into account: no table filters have been defined.");

			return $columns;
		}

		$this->getLogger()->debug("Applying table column filter: user-defined column filters");

		// Get the filters for this table. The table name may be concrete or abstract; I cater for both and merge them.
		$tableName         = $this->getDbo()->replacePrefix($table->getName());
		$abstractTableName = $this->getAbstractTableName($tableName);
		$myFilters         = [];

		if (array_key_exists($tableName, $allUserFilters))
		{
			$myFilters = $allUserFilters[$tableName];
		}

		if (($abstractTableName != $tableName) && array_key_exists($abstractTableName, $allUserFilters))
		{
			$myFilters = array_merge($myFilters, $allUserFilters[$abstractTableName]);
		}

		// No filters? Return the original.
		if (empty($myFilters))
		{
			$this->getLogger()->debug(sprintf("No user-defined column filters were defined for table %s", $tableName));

			return $columns;
		}

		// Perform the actual filtering
		$myFilters = array_unique($myFilters);

		return array_filter($columns, function (Column $column) use ($myFilters) {
			return !in_array($column->getColumnName(), $myFilters);
		});
	}

	/**
	 * Convert a table name to its abstract form, i.e. replacing the prefix with the pseudo-prefix '#__'
	 *
	 * @param   string  $tableName  The real table name
	 *
	 * @return  string  The abstracted table name
	 */
	private function getAbstractTableName($tableName)
	{
		$prefix = $this->getDbo()->getPrefix();
		$prefixLength = strlen($prefix);

		if (strlen($tableName) < $prefixLength)
		{
			return $tableName;
		}

		if (substr($tableName, 0, $prefixLength) == $prefix)
		{
			return '#__' . substr($tableName, $prefixLength);
		}

		return $tableName;
	}
}