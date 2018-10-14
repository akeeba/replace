<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\Controller;

use Akeeba\Replace\WordPress\Model\Job;
use Akeeba\Replace\WordPress\Model\Log as LogModel;
use Akeeba\Replace\WordPress\MVC\Controller\Controller;
use Akeeba\Replace\WordPress\MVC\Model\DataModel;
use Akeeba\Replace\WordPress\View\Log\Html;

class Log extends Controller
{
	public function execute($task = 'default')
	{
		if (in_array($task, ['', 'display', 'default']))
		{
			$task = 'view';
		}

		parent::execute($task); // TODO: Change the autogenerated stub
	}

	/**
	 * Displays the log viewer interface
	 */
	public function view()
	{
		// Get the log ID
		/** @var LogModel $model */
		$ids    = $this->getIDsFromRequest();
		$id     = empty($ids) ? 0 : $ids[0];
		$latest = $this->input->get->getBool('latest', empty($id));
		$model  = $this->model;

		if ($latest)
		{
			$id = $model->getLatestJobId();
		}

		/** @var Html $view */
		$view            = $this->view;
		$view->logId     = $id;
		$view->logSize   = $model->getLogSize($id);
		$view->logTooBig = $view->logSize > 1048576;

		parent::display();
	}

	public function dump()
	{
		// Get the log ID
		/** @var LogModel $model */
		$ids    = $this->getIDsFromRequest();
		$id     = $ids[0];

		/** @var Job $jobModel */
		$jobModel = DataModel::getInstance('Job');
		$logFiles = $jobModel->getAllFiles($id, 'log');

		@ob_end_clean();

		// Remove PHP's time limit
		if (function_exists('ini_get') && function_exists('set_time_limit'))
		{
			if (!@ini_get('safe_mode'))
			{
				@set_time_limit(0);
			}
		}

		@clearstatcache();

		// Send MIME headers
		header('MIME-Version: 1.0');
		header('Content-Type: text/plain');

		flush();

		if (empty($logFiles))
		{
			_e('Sorry, we cannot seem to be able to find a log file for this job. Maybe none was generated or you have already deleted it.', 'akeebareplace');

			exit(0);
		}

		foreach ($logFiles as $file)
		{
			if (!@file_exists($file) || !@is_readable($file))
			{
				continue;
			}

			echo file_get_contents($file);
		}

		exit(0);
	}
}