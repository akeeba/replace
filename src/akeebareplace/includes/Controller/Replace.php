<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\Controller;

use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\WordPress\MVC\Controller\Controller;
use Akeeba\Replace\WordPress\View\Replace\Html;

class Replace extends Controller
{
	/**
	 * Executes before the task is loaded and executed.
	 *
	 * @param   string  $task  The task to execute (passed by reference so we can modify it)
	 */
	public function onBeforeExecute(&$task)
	{
		// The default task in the Replace view is "new" which shows the interface to a new job
		if ($task === 'display')
		{
			$task = 'newJob';
		}
	}

	public function newJob()
	{
		/** @var \Akeeba\Replace\WordPress\Model\Replace $model */
		$model = $this->model;

		// Assign the Configuration object to the View object
		/** @var Html $view */
		$view                = $this->view;
		$view->configuration = $model->getCachedConfiguration();

		// Display the HTML page
		$this->display();
	}
}