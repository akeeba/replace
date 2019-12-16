<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Replace\Engine;

/**
 * Interface to an Engine Part status object
 *
 * @package Akeeba\Replace\Engine
 */
interface StatusInterface
{
	/**
	 * Export the status as an array.
	 *
	 * This is the same "return array" format we use in our other products such as Akeeba Backup, Akeeba Kickstart and
	 * Admin Tools. It's meant to be consumed by client-side JavaScript.
	 *
	 * @return  array
	 */
	public function toArray();


}