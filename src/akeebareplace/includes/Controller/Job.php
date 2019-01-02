<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\Controller;

use Akeeba\Replace\WordPress\Model\Job as JobModel;
use Akeeba\Replace\WordPress\MVC\Controller\DataController;

class Job extends DataController
{
	public function onBeforeBrowse()
	{
		$fltDescription                     = $this->input->getString('description', '');
		$this->view->filters['description'] = $fltDescription;
		$this->model->setState('description', $fltDescription);
	}

	public function deleteFiles()
	{
		if (method_exists($this, 'onBeforeDeleteFiles'))
		{
			$this->onBeforeDeleteFiles();
		}

		$method = strtolower($this->input->getMethod());
		$isPost = $method == 'post';

		if (!$this->csrfProtection('deleteFiles', $isPost))
		{
			@ob_end_clean();
			header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden");

			exit();
		}

		// Get the IDs to delete from the request
		$ids = $this->getIDsFromRequest();

		// Try to delete
		/** @var JobModel $dataModel */
		$dataModel = $this->model;

		foreach ($ids as $id)
		{
			$dataModel->deleteFiles($id);
		}

		$url = admin_url('admin.php?page=akeebareplace&view=' . $this->name);

		$this->view->setTask('display');
		$this->redirect($url);
	}

	public function downloadOutput()
	{
		$this->download('output');
	}

	public function downloadBackup()
	{
		$this->download('backup');
	}

	public function downloadLog()
	{
		$this->download('log');
	}

	private function download($key)
	{
		@ob_end_clean();

		$ids = $this->getIDsFromRequest();
		$id  = 0;

		if (!empty($ids))
		{
			$id = $ids[0];
		}

		/** @var JobModel $model */
		$model = $this->model;
		$files = $model->getAllFiles($id, $key);

		if (empty($files))
		{
			header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
		}

		$canonicalName = basename($files[0]);

		// Remove PHP's time limit
		if (function_exists('ini_get') && function_exists('set_time_limit'))
		{
			if (!@ini_get('safe_mode'))
			{
				@set_time_limit(0);
			}
		}

		@clearstatcache();

		$fileSize = 0;

		foreach ($files as $file)
		{
			if (!@file_exists($file) || !@is_readable($file))
			{
				continue;
			}

			$thisSize = @filesize($file);
			$fileSize += ($thisSize === false) ? 0 : $thisSize;
		}

		// Send MIME headers
		header('MIME-Version: 1.0');
		header('Content-Disposition: attachment; filename="' . $canonicalName . '"');
		header('Content-Transfer-Encoding: text');
		header('Content-Type: text/plain');
		header('Content-Length: ' . $fileSize);

		// Disable caching
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Expires: 0");
		header('Pragma: no-cache');

		flush();

		foreach ($files as $file)
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