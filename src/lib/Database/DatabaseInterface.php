<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
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
