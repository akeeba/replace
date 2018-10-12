<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\Controller;

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
		// TODO Implement files-only deletion
		die('TODO');
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