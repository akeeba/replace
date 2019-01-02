<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Database;

/**
 * Database Interface
 *
 * @codeCoverageIgnore
 */
interface DatabaseInterface
{
	/**
	* Test to see if the connector is available.
	*
	* @return  boolean  True on success, false otherwise.
	*/
	public static function isSupported();
}
