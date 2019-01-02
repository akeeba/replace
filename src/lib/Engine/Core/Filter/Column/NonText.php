<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */


namespace Akeeba\Replace\Engine\Core\Filter\Column;


use Akeeba\Replace\Database\Metadata\Column;
use Akeeba\Replace\Database\Metadata\Table;

/**
 * A filter to excluded non-text columns. Since Akeeba Replace is database *text* data replacement software it makes
 * sense that we do not try to replace non-text rows. Right?
 *
 * @package  Akeeba\Replace\Engine\Core\Filter\Column
 */
class NonText extends AbstractFilter
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
		$this->getLogger()->debug("Applying table column filter: non-text columns");

		return array_filter($columns, function($column) {
			/** @var Column $column */
			return $column->isText();
		});
	}

}