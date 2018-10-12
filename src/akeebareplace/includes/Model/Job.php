<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
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

		$helper     = new OutFileSetup();
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
}