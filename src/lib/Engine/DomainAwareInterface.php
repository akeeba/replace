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
 * Interface to an object that's aware of engine domains.
 *
 * This is used by parts which process a chain of other engine parts. The engine Domain is the description of the engine
 * part currently executing in the outermost part we are talking to.
 *
 * @package Akeeba\Replace\Engine
 */
interface DomainAwareInterface
{
	/**
	 * Get the name of the engine domain this part is processing.
	 *
	 * @return  mixed
	 */
	public function getDomain();
}