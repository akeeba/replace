<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine;

/**
 * An Interface for objects implementing the Observable behaviour (Observer patterns)
 *
 * @package Akeeba\Replace\Engine
 */
interface ObservableInterface
{
	/**
	 * Add an observer to the object for a given event name
	 *
	 * @param   string    $event     The event the observer will be handling
	 * @param   callable  $observer  The observer. Can be an anonymous function or any other callable supported by PHP
	 *
	 * @return  void
	 */
	public function addObserver($event, callable $observer);

	/**
	 * Triggers the observers for a specific event. Do NOT pass $this to the parameters. It will always be added as the
	 * first argument to the observers.
	 *
	 * The observers are not supposed to have a return value. Any return value must be exported by reference to one of
	 * the parameters. For example:
	 * $this->trigger('multiply', [11, 4, &$result])
	 * implies $result is an in/out parameter which can be used to return a result to the caller, e.g.
	 * echo $result; // This could for example print '44'
	 *
	 * @param   string  $event
	 * @param   array   $parameters
	 *
	 * @return  void
	 */
	public function trigger($event, array $parameters);
}