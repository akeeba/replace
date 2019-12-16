<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Replace\Timer;

/**
 * A Trait to implement the TimerAwareInterface
 *
 * @package Akeeba\Replace\Timer
 */
trait TimerAware
{
	/**
	 * The timer object
	 *
	 * @var  TimerInterface
	 */
	protected $timer = null;

	/**
	 * Assigns a Timer object.
	 *
	 * This should only be used internally by the constructor. The constructor itself should use explicit dependency
	 * injection.
	 *
	 * @param   TimerInterface  $timer  The timer object to assign
	 *
	 * @return  void
	 */
	public function setTimer(TimerInterface $timer)
	{
		$this->timer = $timer;
	}

	/**
	 * Returns a reference to the timer object. This should only be used internally.
	 *
	 * @return  TimerInterface
	 */
	public function getTimer()
	{
		return $this->timer;
	}
}