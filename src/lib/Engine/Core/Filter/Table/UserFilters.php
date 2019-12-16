<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Replace\Engine\Core\Filter\Table;

/**
 * User-defined table filters
 *
 * Filter out the tables based on user-defined criteria
 *
 * @package Akeeba\Replace\Engine\Core\Filter\Table
 */
class UserFilters extends AbstractFilter
{
	/**
	 * Filter the table list, returning the filtered result
	 *
	 * @param   array  $tables
	 *
	 * @return  array
	 */
	public function filter(array $tables)
	{
		$tableFilters = $this->getConfig()->getExcludeTables();

		if (empty($tableFilters))
		{
			$this->getLogger()->debug("Table filters will NOT be taken into account: no table filters have been defined.");

			return $tables;
		}

		// Convert table filters from abstract to concrete names. Lets you use filters like '#__foo' instead of 'wp_foo'
		$db           = $this->getDbo();
		$tableFilters = array_map(function ($v) use ($db) {
			return $db->replacePrefix($v);
		}, $tableFilters);

		$this->getLogger()->debug("Applying table filter: excluded tables");

		return array_filter($tables, function ($tableName) use ($tableFilters) {
			if (in_array($tableName, $tableFilters))
			{
				$this->getLogger()->debug("Skipping table $tableName");

				return false;
			}

			return true;
		});
	}

}