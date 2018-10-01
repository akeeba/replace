<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\Helper;


use Akeeba\Replace\WordPress\Model\Replace;

abstract class Form
{
	/**
	 * Returns an HTML select for the supported database collations
	 *
	 * @param   string  $name      Name of the HTML form field
	 * @param   string  $id        ID of the HTML form field
	 * @param   string  $selected  Currently selected value
	 *
	 * @return  string
	 */
	public static function selectCollation($name, $id, $selected = '')
	{
		$model      = new Replace();
		$collations = $model->getCollations();

		$options = [
			'' => __('(no change)', 'akeebareplace')
		];

		array_walk($collations, function ($collation) use (&$options) {
			$options[$collation] = $collation;
		});

		$html = '<select name="' . $name . '" id="' . $id . '">' . "\n";

		array_walk($options, function($text, $value) use (&$html, $selected) {
			$attributes = ($selected == $value) ? ' selected="selected"' : '';
			$html .= "\t<option value=\"$value\"$attributes>$text</option>\n";
		});

		$html .= "</select>\n";

		return $html;
	}

	public static function selectExcludeTables($name, $id, $selected = [], $allTables = false)
	{
		$model  = new Replace();
		$tables = $model->getDatabaseTables($allTables);

		array_unshift($tables, '');

		$html = '<select name="' . $name . '" id="' . $id . '" multiple="multiple" size="10">' . "\n";

		array_walk($tables, function ($value) use (&$html, $selected) {
			$attributes = in_array($value, $selected) ? ' selected="selected"' : '';
			$text       = empty($value) ? __('(none)', 'akeebareplace') : $value;
			$html       .= "\t<option value=\"$value\"$attributes>$text</option>\n";
		});

		$html .= "</select>\n";

		return $html;
	}
}