<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Observable;


interface ObservableInterface
{
	/**
	 * Attach an observer to the object
	 *
	 * @param   ObserverInterface  $observer
	 *
	 * @return  void
	 */
	public function attach(ObserverInterface $observer);

	/**
	 * Detach (delete) an observer from the object
	 *
	 * @param   ObserverInterface  $observer
	 *
	 * @return  void
	 */
	public function detach(ObserverInterface $observer);

	/**
	 * Notify the observer(s) about something.
	 *
	 * @param   string  $message  The message to pass to the observers
	 *
	 * @return  void
	 */
	public function notify($message);
}