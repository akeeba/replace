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
 * A Trait to implement the LoggerAwareInterface
 *
 * @package Akeeba\Replace\Logger
 */
trait LoggerAware
{
	/**
	 * The logger object used to log things in this class
	 *
	 * @var  LoggerInterface
	 */
	private $logger = null;

	/**
	 * Assigns a Logger to the object.
	 *
	 * This should only be used internally by the constructor. The constructor itself should use explicit dependency
	 * injection.
	 *
	 * @param   LoggerInterface  $logger  The logger object to assign
	 *
	 * @return  void
	 */
	protected function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * Returns a reference to the logger object. This should only be used internally.
	 *
	 * @return  LoggerInterface
	 */
	public function getLogger()
	{
		return $this->logger;
	}
}