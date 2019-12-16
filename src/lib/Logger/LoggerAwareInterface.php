<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Replace\Logger;

/**
 * Interface to a class which knows about using a logger
 *
 * @package Akeeba\Replace\Logger
 */
interface LoggerAwareInterface
{
	/**
	 * Returns a reference to the logger object. This should only be used internally.
	 *
	 * @return  LoggerInterface
	 */
	public function getLogger();
}