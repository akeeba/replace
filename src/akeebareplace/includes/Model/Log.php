<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\Model;


use Akeeba\Replace\WordPress\MVC\Model\DataModel;
use Akeeba\Replace\WordPress\MVC\Model\Model;

class Log extends Model
{
	/**
	 * Get the ID of the job last run
	 *
	 * @param   \wpdb  $db
	 *
	 * @return  int  The job ID or 0 if no job has run yet
	 */
	public function getLatestJobId($db = null)
	{
		/** @var Job $model */
		$model = DataModel::getInstance('Job', $db);
		$model->setState('orderBy', 'id');
		$model->setState('orderDir', 'DESC');
		$items = $model->getItems(false, 0, 1);

		if (empty($items))
		{
			return 0;
		}

		return $items[0]->id;
	}

	/**
	 * Get the total log file(s) size in bytes
	 *
	 * @param   int  $id  The Job ID for the log file
	 *
	 * @return  int
	 */
	public function getLogSize($id)
	{
		$size = 0;
		/** @var Job $jobModel */
		$jobModel = DataModel::getInstance('Job');
		$logFiles = $jobModel->getAllFiles($id, 'log');

		if (empty($logFiles))
		{
			return $size;
		}

		@clearstatcache();

		foreach ($logFiles as $file)
		{
			if (!@file_exists($file) || !@is_readable($file))
			{
				continue;
			}

			$size += filesize($file);
		}

		return $size;
	}
}