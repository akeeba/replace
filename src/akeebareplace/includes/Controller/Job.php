<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
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
		// TODO Implement me
		die('TODO');
	}

	public function downloadBackup()
	{
		// TODO Implement me
		die('TODO');
	}
}