<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */


namespace Akeeba\Replace\Engine\ErrorHandling;

/**
 * Interface to objects which support reporting non-show-stopper warnings known to them
 *
 * @package Akeeba\Replace\Engine
 */
interface WarningsAwareInterface
{
	/**
	 * Return the warning exceptions known to the object implementing this interface
	 *
	 * @return  WarningException[]
	 */
	public function getWarnings();

	/**
	 * Adds a warning exception to the queue.
	 *
	 * @param   WarningException  $e  The warning exception to add to the queue
	 *
	 * @return  void
	 */
	public function addWarning(WarningException $e);

	/**
	 * Adds a warning to the queue from a warning message string. This creates a WarningException, adds it to the queue
	 * and returns it to the caller.
	 *
	 * @param   string  $message  The warning message to add
	 *
	 * @return  WarningException
	 */
	public function addWarningMessage($message);

	/**
	 * Clears the warnings queue
	 *
	 * @return  void
	 */
	public function resetWarnings();

	/**
	 * Inherits the warnings from another WarningsAware object and clears its queue
	 *
	 * @param   WarningsAwareInterface $object The object to inherit from
	 *
	 * @return  void
	 */
	public function inheritWarningsFrom(WarningsAwareInterface $object);
}