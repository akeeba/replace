<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\Model;

use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Helper\OutFileSetup;
use Akeeba\Replace\WordPress\MVC\Model\DataModel;
use wpdb;

/**
 * Database-aware model for the #__akeebareplace_jobs table which lists all the replacement jobs which have executed.
 *
 * The fields in the table are as follows:
 *
 * id           automatically incrementing integer key
 * description  description for this job
 * options      serialized array with the Configuration options
 * created_on   when this row was created
 * run_on       last time we ran this job
 *
 * @package  Akeeba\Replace\WordPress\Model
 */
class Job extends DataModel
{
	public function __construct(wpdb $db)
	{
		global $wpdb;

		$this->tableName = $wpdb->prefix . 'akeebareplace_jobs';
		$this->pkName    = 'id';

		parent::__construct($db);
	}

	/**
	 * Builds the query for retrieving rows. Overridden to support filters.
	 *
	 * @return  string
	 */
	public function buildQuery()
	{
		global $wpdb;

		// Get the default query and initialize the WHERE clauses and filter values
		$query   = parent::buildQuery();
		$where   = [];
		$filters = [];

		// Apply the Description filter
		$fltDescription = $this->getState('description', '');

		if (!empty($fltDescription))
		{
			$where[] = '`description` = %s';
			$filters[] = $fltDescription;
		}

		// Apply the WHERE clauses using wpdp::prepare() -- the preferred way since WP 3.6.0
		if (!empty($where))
		{
			$query .= ' WHERE ' . implode(' AND ', $where);
			$query = $wpdb->prepare($query, $filters);
		}

		return $query;
	}

	/**
	 * Post-processes the query before applying the LIMIT. This is used to set up the ORDER BY clause.
	 *
	 * @param   string  $query  The query to post-process
	 *
	 * @return  string
	 */
	public function onBeforeApplyLimit($query)
	{
		$orderBy  = $this->getState('orderBy', null, 'cmd');
		$orderDir = $this->getState('orderDir', 'ASC', 'cmd');

		if (empty($orderBy))
		{
			return $query;
		}

		$orderDir = trim(strtoupper($orderDir));
		$orderDir = !in_array($orderDir, ['ASC', 'DESC']) ? 'ASC' : $orderDir;

		$query .= " ORDER BY `{$orderBy}` $orderDir";

		return $query;
	}

	/**
	 * Returns the log, output and backup file paths for a job record. If the file does not exist the entry is blank
	 * for that file.
	 *
	 * @param   \stdClass  $record  The job record
	 *
	 * @return  array
	 */
	public function getFilePathsForRecord($record)
	{
		$files = [
			'log'    => '',
			'output' => '',
			'backup' => '',
		];

		if (is_array($record))
		{
			$record = (object) $record;
		}

		if (!isset($record->run_on))
		{
			return $files;
		}

		$lastRun = $record->run_on;

		if (empty($lastRun))
		{
			return $files;
		}

		$wpTimezone = get_option('timezone_string', 'UTC');
		$helper     = new OutFileSetup(null, $wpTimezone);
		$config     = new Configuration(unserialize($record->options));
		$additional = [
			'[OUTPUT_PATH]' => plugin_dir_path(AKEEBA_REPLACE_SELF) . 'output/',
		];

		$files['log']    = $helper->replaceVariables($config->getLogFile(), $additional, $lastRun);
		$files['output'] = $helper->replaceVariables($config->getOutputSQLFile(), $additional, $lastRun);
		$files['backup'] = $helper->replaceVariables($config->getBackupSQLFile(), $additional, $lastRun);

		if (!file_exists($files['log']))
		{
			$files['log'] = '';
		}

		if (!file_exists($files['output']))
		{
			$files['output'] = '';
		}

		if (!file_exists($files['backup']))
		{
			$files['backup'] = '';
		}

		return $files;
	}

	/**
	 * Runs before deleting a record from the database. Used to automatically delete the associated files as well. We
	 * need to run this before deleting the record since loading the record is a requirement to finding out which files
	 * we need to delete.
	 *
	 * @param   int  $id
	 */
	public function onBeforeDelete($id)
	{
		$this->deleteFiles($id);
	}

	/**
	 * Delete all files (log, output, backup) for a record
	 *
	 * @param   int  $id  The record ID
	 */
	public function deleteFiles($id)
	{
		$record = $this->getItem($id);

		if (empty($record))
		{
			return;
		}

		$files = $this->getFilePathsForRecord($record);
		$keys  = ['log', 'output', 'backup'];

		foreach ($keys as $key)
		{
			$baseFile = $files[$key];

			if (empty($baseFile))
			{
				continue;
			}

			$partNumber = 0;

			while (true)
			{
				$thisFile = $this->getPartPath($baseFile, $partNumber);

				if (!file_exists($thisFile))
				{
					break;
				}

				$partNumber++;
				@unlink($thisFile);
			}
		}
	}

	/**
	 * Get the filename for a part number. Part numbers start with zero.
	 *
	 * @param   string  $filePath    The base path of the file
	 * @param   int     $partNumber  The sequential part number
	 *
	 * @return  string
	 */
	protected function getPartPath($filePath, $partNumber)
	{
		if ($partNumber == 0)
		{
			return $filePath;
		}

		$dirName   = dirname($filePath);
		$baseName  = basename($filePath);
		$extension = '';
		$dotPos    = strrpos($baseName, '.');

		if ($dotPos !== false)
		{
			$extension = substr($baseName, $dotPos);
			$baseName  = substr($baseName, 0, $dotPos);
		}

		if (strlen($extension) == 0)
		{
			/**
			 * No extension: files are number foo, foo.01, foo.02, ...
			 */
			$extension = '.' . sprintf('%02u', $partNumber);
		}
		elseif ($extension == '.php')
		{
			/**
			 * With PHP extension: .php, .01.php, .02.php, ...
			 */
			$extension = '.' . sprintf('%02u', $partNumber) . '.php';
		}
		else
		{
			/**
			 * With extension: .sql, .s01, .s02, ...
			 */
			$extension = substr($extension, 0, -2) . sprintf('%02u', $partNumber);
		}

		return $dirName . '/' . $baseName . $extension;
	}

	/**
	 * Get all files for a specific job and area
	 *
	 * @param   int     $id   The ID of the job
	 * @param   string  $key  The area you want files for: log, output, backup
	 *
	 * @return  array  List of files
	 */
	public function getAllFiles($id, $key = 'log')
	{
		$record = $this->getItem($id);

		if (empty($record))
		{
			return [];
		}

		$files    = $this->getFilePathsForRecord($record);
		$baseFile = $files[$key];

		if (empty($baseFile))
		{
			return [];
		}

		$partNumber = 0;
		$ret = [];

		while (true)
		{
			$thisFile = $this->getPartPath($baseFile, $partNumber);

			if (!file_exists($thisFile))
			{
				break;
			}

			$partNumber++;
			$ret[] = $thisFile;
		}

		return $ret;
	}
}