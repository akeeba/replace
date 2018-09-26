<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
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