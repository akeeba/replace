<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
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