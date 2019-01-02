<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core\Filter\Table;

/**
 * Filter non-core tables.
 *
 * Filter out the tables which do not start with the configured prefix. If the configuration parameter allTables
 * is set this filter does nothing.
 *
 * @package Akeeba\Replace\Engine\Core\Filter\Table
 */
class NonCore extends AbstractFilter
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
		if ($this->getConfig()->isAllTables())
		{
			$this->getLogger()->debug("Non-core table filters will NOT be taken into account: allTables is true.");

			return $tables;
		}

		$prefix       = $this->getDbo()->getPrefix();
		$prefixLength = strlen($prefix);

		$this->getLogger()->debug("Applying table filter: non-core");

		return array_filter($tables, function ($tableName) use ($prefix, $prefixLength) {
			if (strlen($tableName) < ($prefixLength + 1))
			{
				return false;
			}

			if (substr($tableName, 0, $prefixLength) != $prefix)
			{
				$this->getLogger()->debug("Skipping table $tableName");

				return false;
			}

			return true;
		});
	}

}