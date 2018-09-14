<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Observable;

/**
 * Interface to an engine Observer.
 *
 * Observers receive notifications from an observable object to perform various tasks. This allows extensibility of the
 * observable object while maintaining separation of concerns.
 *
 * @package Akeeba\Replace\Engine
 */
interface ObserverInterface
{
	/**
	 * The main method of an Observer. This is called whenever the engine part has something to tell you.
	 *
	 * @param   object  $object   The object which triggered the observer notification
	 * @param   string  $message  The message passed by the object to the observer
	 *
	 * @return  void
	 */
	public function update(&$object, $message);
}