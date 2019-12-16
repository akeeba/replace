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
 * A trait for objects which have a database connection object
 *
 * @package Akeeba\Replace\Database
 */
trait DatabaseAware
{
	/**
	 * The database connection known to this object
	 *
	 * @var  Driver
	 */
	protected $db;

	/**
	 * Set the database driver object
	 *
	 * @param   Driver   $db
	 *
	 * @return  void
	 */
	protected function setDriver(Driver $db)
	{
		$this->db = $db;
	}

	/**
	 * Return the database driver object
	 *
	 * @return  Driver
	 */
	public function getDbo()
	{
		return $this->db;
	}
}