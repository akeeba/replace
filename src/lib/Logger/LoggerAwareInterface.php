<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
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
	 * Assigns a Logger to the object.
	 *
	 * This should only be used internally by the constructor. The constructor itself should use explicit dependency
	 * injection.
	 *
	 * @param   LoggerInterface  $logger  The logger object to assign
	 *
	 * @return  void
	 */
	public function setLogger(LoggerInterface $logger);

	/**
	 * Returns a reference to the logger object. This should only be used internally.
	 *
	 * @return  LoggerInterface
	 */
	public function getLogger();
}