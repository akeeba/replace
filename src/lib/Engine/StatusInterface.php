<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
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