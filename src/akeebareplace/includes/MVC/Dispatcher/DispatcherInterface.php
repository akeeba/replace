<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\MVC\Dispatcher;


use Akeeba\Replace\WordPress\MVC\Input\InputInterface;

interface DispatcherInterface
{
	/**
	 * Dispatcher constructor.
	 *
	 * @param   InputInterface  $input  The Input data object. Null to get a new one from the request & server data.
	 *
	 * @return  void
	 */
	public function __construct($input = null);

	/**
	 * Routes the application.
	 *
	 * Finds the view and task to use. Loads and instantiates the Controller. Executes the task in the Controller.
	 *
	 * @return  void
	 */
	public function route();
}