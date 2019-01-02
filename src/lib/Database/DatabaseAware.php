<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
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