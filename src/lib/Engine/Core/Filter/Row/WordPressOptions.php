<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core\Filter\Row;

/**
 * Row filter for the WordPress options table
 *
 * @package  Akeeba\Replace\Engine\Core\Filter\Row
 */
class WordPressOptions extends AbstractFilter
{
	private $whiteListOptionNames = [
		// WordPress 6.3: Core blocks CSS files
		'_transient_wp_core_block_css_files',
	];

	/**
	 * Check whether the table row should be processed or not
	 *
	 * @param   $tableName  string  The name of the table being processed
	 * @param   $row        array   The row being processed
	 *
	 * @return  bool  True to allow processing
	 */
	public function filter($tableName, array $row)
	{
		// This filter only applies to the options table
		if ($tableName != $this->db->getPrefix() . 'options')
		{
			return true;
		}

		$name = $row['option_name'];

		// Explicitly allow replacements on some transient options
		if (in_array($name, $this->whiteListOptionNames))
		{
			return true;
		}

		// Explicitly allow replacements on block patterns' transients
		if (strpos($name, '_site_transient_wp_remote_block_patterns_') !== false)
		{
			return true;
		}

		// Do not replace data in the field used to temporarily store the engine cache
		if ($name === 'akeebareplace_engine_cache')
		{
			return false;
		}

		// Do not replace data in site transients
		if (strpos($name, '_site_transient_') === 0)
		{
			return false;
		}

		// Do not replace data in transients
		if (strpos($name, '_transient') === 0)
		{
			return false;
		}

		return true;
	}
}