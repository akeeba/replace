<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Replace\Database;


interface DatabaseAwareInterface
{
	/**
	 * Return the database driver object
	 *
	 * @return  Driver
	 */
	public function getDbo();
}