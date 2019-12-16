<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */


namespace Akeeba\Replace\Engine\ErrorHandling;

/**
 * Interface to objects which support reporting show-stopper errors known to them
 *
 * @package  Akeeba\Replace\Engine
 */
interface ErrorAwareInterface
{
	/**
	 * Return the latest error exception thrown by the object implementing this interface
	 *
	 * @return  ErrorException
	 */
	public function getError();

	/**
	 * Sets the error exception to the object
	 *
	 * @param   ErrorException  $e  The error to set
	 *
	 * @return  void
	 */
	public function setError(ErrorException $e);

	/**
	 * Sets the error from an error message string. This creates an ErrorException, assigns it to the object and returns
	 * it to the caller.
	 *
	 * @param   string  $message
	 *
	 * @return  ErrorException
	 */
	public function setErrorMessage($message);

	/**
	 * Clears the error
	 *
	 * @return  void
	 */
	public function resetError();

	/**
	 * Inherits the error from another ErrorAware object and clears its error
	 *
	 * @param   ErrorAwareInterface $object The object to inherit from
	 *
	 * @return  void
	 */
	public function inheritErrorFrom(ErrorAwareInterface $object);
}