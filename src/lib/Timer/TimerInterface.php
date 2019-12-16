<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Replace\Timer;


interface TimerInterface
{
	/**
	 * Public constructor, creates the timer object and calculates the execution
	 * time limits.
	 *
	 * @param   integer  $max_exec_time  Maximum execution time, in seconds
	 * @param   integer  $runtime_bias   Runtime bias factor, as percent points of the max execution time
	 *
	 * @return  void
	 */
	public function __construct($max_exec_time = 5, $runtime_bias = 75);

	/**
	 * Gets the number of seconds left, before we hit the "must break" threshold
	 *
	 * @return  float
	 */
	public function getTimeLeft();

	/**
	 * Gets the time elapsed since object creation/unserialization, effectively
	 * how long this step is running
	 *
	 * @return  float
	 */
	public function getRunningTime();

	/**
	 * Reset the timer
	 *
	 * @return  void
	 */
	public function resetTime();
}