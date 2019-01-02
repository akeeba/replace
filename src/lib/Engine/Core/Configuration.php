<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core;

use Akeeba\Replace\Logger\LoggerInterface;

/**
 * Configuration for Akeeba Replace's core engine.
 *
 * The purpose of this dumb class is to provide a single point of collection for the configuration of an Akeeba Replace
 * job. These configuration parameters will be used by the actual domain objects which do work. This lets us have less
 * verbose constructors at the expense of introducing an Anemic Domain Model anti-pattern. It's still better than magic
 * key-value arrays which provide no validation.
 *
 * @package Akeeba\Replace\Engine\Core
 */
class Configuration
{
	/**
	 * Output SQL file path. Empty = no SQL output.
	 *
	 * @var  string
	 */
	private $outputSQLFile = '';

	/**
	 * Backup SQL file path. Empty = no backup.
	 *
	 * @var  string
	 */
	private $backupSQLFile = '';

	/**
	 * Log file path. Empty = no log.
	 *
	 * @var  string
	 */
	private $logFile = '';

	/**
	 * Minimum severity level to report to the log
	 *
	 * @var  int
	 */
	private $minLogLevel = LoggerInterface::SEVERITY_DEBUG;

	/**
	 * Should I run actions directly to the database?
	 *
	 * @var  bool
	 */
	private $liveMode = true;

	/**
	 * Include all tables, regardless of their prefix. False = only those matching the configured prefix.
	 *
	 * @var  bool
	 */
	private $allTables = false;

	/**
	 * Maximum number of database rows to process at once
	 *
	 * @var  int
	 */
	private $maxBatchSize = 1000;

	/**
	 * Table names to exclude. Either abstract (#__table) or concrete (wp_table) name accepted
	 *
	 * @var  string[]
	 */
	private $excludeTables = [];

	/**
	 * Table rows to exclude. Format:
	 * [
	 *   '#__table1' => ['row1', 'row2', ],
	 *   '#__table2' => ['rowA', 'rowB', ],
	 *   // ...
	 * ]
	 *
	 * @var  string[]
	 */
	private $excludeRows = [];

	/**
	 * Use regular expressions?
	 *
	 * @var  bool
	 */
	private $regularExpressions = false;

	/**
	 * Replacements to perform. Format:
	 * [
	 *   'from 1' => 'to 1',
	 *   'from 2' => 'to 2',
	 *   //...
	 * ]
	 *
	 * @var  array
	 */
	private $replacements = [];

	/**
	 * Change the database collation. Empty = do not change. Can fail without error.
	 *
	 * @var  string
	 */
	private $databaseCollation = '';

	/**
	 * Change the table / column collation. Empty = do not change. Can fail without error.
	 *
	 * @var  string
	 */
	private $tableCollation = '';

	/**
	 * The human-readable description for the backup job which will be recorded in the database
	 *
	 * @var  string
	 */
	private $description = '';

	/**
	 * Configuration constructor.
	 *
	 * Creates a Configuration object from a configuration keyed array.
	 *
	 * @param   array  $params  A key-value array with the configuration variables.
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct(array $params)
	{
		$this->setFromParameters($params);
	}

	/**
	 * Return the output SQL file path. Empty = no SQL output
	 *
	 * @return  string
	 *
	 * @codeCoverageIgnore
	 */
	public function getOutputSQLFile()
	{
		return $this->outputSQLFile;
	}

	/**
	 * Set the output SQL file path. Empty = no SQL output
	 *
	 * @param   string  $outputSQLFile
	 *
	 * @return  Configuration
	 *
	 * @codeCoverageIgnore
	 */
	protected function setOutputSQLFile($outputSQLFile)
	{
		if (!is_string($outputSQLFile))
		{
			return $this;
		}

		$this->outputSQLFile = $outputSQLFile;

		return $this;
	}

	/**
	 * Get the backup SQL file path. Empty = no backup
	 *
	 * @return  string
	 *
	 * @codeCoverageIgnore
	 */
	public function getBackupSQLFile()
	{
		return $this->backupSQLFile;
	}

	/**
	 * Set the backup SQL file path. Empty = no backup
	 *
	 * @param   string  $backupSQLFile
	 *
	 * @return  Configuration
	 *
	 * @codeCoverageIgnore
	 */
	protected function setBackupSQLFile($backupSQLFile)
	{
		if (!is_string($backupSQLFile))
		{
			return $this;
		}

		$this->backupSQLFile = $backupSQLFile;

		return $this;
	}

	/**
	 * Get the log file pathname
	 *
	 * @return  string
	 *
	 * @codeCoverageIgnore
	 */
	public function getLogFile()
	{
		return $this->logFile;
	}

	/**
	 * Set the log file pathname
	 *
	 * @param  string  $logFile
	 *
	 * @codeCoverageIgnore
	 */
	protected function setLogFile($logFile)
	{
		$this->logFile = $logFile;
	}

	/**
	 * Get the minimum log level
	 *
	 * @return  int
	 *
	 * @codeCoverageIgnore
	 */
	public function getMinLogLevel()
	{
		return $this->minLogLevel;
	}

	/**
	 * Set the minimum log level
	 *
	 * @param  int  $minLogLevel
	 *
	 * @codeCoverageIgnore
	 */
	protected function setMinLogLevel($minLogLevel)
	{
		$this->minLogLevel = $minLogLevel;
	}

	/**
	 * Should I run actions directly to the database?
	 *
	 * @return  bool
	 *
	 * @codeCoverageIgnore
	 */
	public function isLiveMode()
	{
		return $this->liveMode;
	}

	/**
	 * Tell me whether I should run actions directly to the database
	 *
	 * @param   bool  $liveMode
	 *
	 * @return  Configuration
	 *
	 * @codeCoverageIgnore
	 */
	protected function setLiveMode($liveMode)
	{
		$liveMode = is_bool($liveMode) ? $liveMode : ($liveMode == 1);

		$this->liveMode = $liveMode;

		return $this;
	}

	/**
	 * Should I include all tables, regardless of their prefix?
	 *
	 * @return  bool
	 *
	 * @codeCoverageIgnore
	 */
	public function isAllTables()
	{
		return $this->allTables;
	}

	/**
	 * Tell me whether I should include all tables, regardless of their prefix.
	 *
	 * @param   bool  $allTables  False = include only those tables matching the configured prefix.
	 *
	 * @return  Configuration
	 *
	 * @codeCoverageIgnore
	 */
	protected function setAllTables($allTables)
	{
		$allTables = is_bool($allTables) ? $allTables : ($allTables == 1);

		$this->allTables = $allTables;

		return $this;
	}

	/**
	 * Table names to exclude. Both abstract (#__table) or concrete (wp_table) names may be be returned.
	 *
	 * @return  string[]
	 *
	 * @codeCoverageIgnore
	 */
	public function getExcludeTables()
	{
		return $this->excludeTables;
	}

	/**
	 * Set the table names to exclude. Either abstract (#__table) or concrete (wp_table) name accepted.
	 *
	 * @param   string[]  $excludeTables
	 *
	 * @return  Configuration
	 */
	protected function setExcludeTables(array $excludeTables)
	{
		$this->excludeTables = [];

		foreach ($excludeTables as $table)
		{
			if (!is_string($table))
			{
				continue;
			}

			$table = trim($table);

			if (empty($table))
			{
				continue;
			}

			$this->excludeTables[] = $table;
		}

		$this->excludeTables = array_unique($this->excludeTables);

		return $this;
	}

	/**
	 * Get the rows to exclude per table. Format: ['table' => ['row1', 'row2', ...], ...]
	 *
	 * @return  string[]
	 *
	 * @codeCoverageIgnore
	 */
	public function getExcludeRows()
	{
		return $this->excludeRows;
	}

	/**
	 * Set the rows to exclude per table. Format: ['table' => ['row1', 'row2', ...], ...]
	 *
	 * @param   string[]  $excludeRows
	 *
	 * @return  Configuration
	 */
	protected function setExcludeRows(array $excludeRows)
	{
		$this->excludeRows = [];

		foreach ($excludeRows as $table => $rows)
		{
			if (!is_array($rows))
			{
				continue;
			}

			if (empty($rows))
			{
				continue;
			}

			if (!is_string($table))
			{
				continue;
			}

			$table = trim($table);

			if (empty($table))
			{
				continue;
			}

			$addRows = [];

			foreach ($rows as $row)
			{
				if (!is_string($row))
				{
					continue;
				}

				$row = trim($row);

				if (empty($row))
				{
					continue;
				}

				$addRows[] = $row;
			}

			if (empty($addRows))
			{
				continue;
			}

			if (!isset($this->excludeRows[$table]))
			{
				$this->excludeRows[$table] = [];
			}

			$this->excludeRows[$table] = array_merge($this->excludeRows[$table], $addRows);
		}

		$this->excludeRows = array_map(function ($rows) {
			return array_unique($rows);
		}, $this->excludeRows);

		return $this;
	}

	/**
	 * Are the replace-from clauses regular expressions? If false, they are treated as plain text.
	 *
	 * @return  bool
	 *
	 * @codeCoverageIgnore
	 */
	public function isRegularExpressions()
	{
		return $this->regularExpressions;
	}

	/**
	 * Tell me whether the replace-from clauses are regular expressions.
	 *
	 * @param   bool  $regularExpressions
	 *
	 * @return  Configuration
	 *
	 * @codeCoverageIgnore
	 */
	protected function setRegularExpressions($regularExpressions)
	{
		$regularExpressions = is_bool($regularExpressions) ? $regularExpressions : ($regularExpressions == 1);

		$this->regularExpressions = $regularExpressions;

		return $this;
	}

	/**
	 * Get the replacement pairs (['from' => 'to', ...])
	 *
	 * @return  array
	 *
	 * @codeCoverageIgnore
	 */
	public function getReplacements()
	{
		return $this->replacements;
	}

	/**
	 * Set the replacement pairs  (['from' => 'to', ...])
	 *
	 * @param   array  $replacements
	 *
	 * @return  Configuration
	 *
	 * @codeCoverageIgnore
	 */
	protected function setReplacements(array $replacements)
	{
		if (!is_array($replacements))
		{
			return $this;
		}

		$this->replacements = $replacements;

		return $this;
	}

	/**
	 * Get the database collation to change to. Empty = do not change.
	 *
	 * @return  string
	 *
	 * @codeCoverageIgnore
	 */
	public function getDatabaseCollation()
	{
		return $this->databaseCollation;
	}

	/**
	 * Set the database collation to change to. Empty = do not change.
	 *
	 * @param   string  $databaseCollation
	 *
	 * @return  Configuration
	 *
	 * @codeCoverageIgnore
	 */
	protected function setDatabaseCollation($databaseCollation)
	{
		if (!is_string($databaseCollation))
		{
			return $this;
		}

		$this->databaseCollation = $databaseCollation;

		return $this;
	}

	/**
	 * Get the table and row collation to change to. Empty = do not change.
	 *
	 * @return  string
	 *
	 * @codeCoverageIgnore
	 */
	public function getTableCollation()
	{
		return $this->tableCollation;
	}

	/**
	 * Set the table and row collation to change to. Empty = do not change.
	 *
	 * @param   string  $tableCollation
	 *
	 * @return  Configuration
	 *
	 * @codeCoverageIgnore
	 */
	protected function setTableCollation($tableCollation)
	{
		if (!is_string($tableCollation))
		{
			return $this;
		}

		$this->tableCollation = $tableCollation;

		return $this;
	}

	/**
	 * Get the maximum number of rows to process at once
	 *
	 * @return  int
	 *
	 * @codeCoverageIgnore
	 */
	public function getMaxBatchSize()
	{
		return $this->maxBatchSize;
	}

	/**
	 * Set the maximum number of rows to process at once
	 *
	 * @param   int  $maxBatchSize
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	protected function setMaxBatchSize($maxBatchSize)
	{
		$this->maxBatchSize = max((int) $maxBatchSize, 1);
	}

	/**
	 * Populates the Configuration from a key-value parameters array.
	 *
	 * @param   array  $params  A key-value array with the configuration variables.
	 *
	 * @return void
	 */
	protected function setFromParameters(array $params)
	{
		if (empty($params))
		{
			return;
		}

		foreach ($params as $k => $v)
		{
			if (!property_exists($this, $k))
			{
				continue;
			}

			$method = 'set' . ucfirst($k);

			if (!method_exists($this, $method))
			{
				continue;
			}

			call_user_func_array([$this, $method], [$v]);
		}
	}

	/**
	 * Get the human-readable description
	 *
	 * @return  string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Set the human-readable description
	 *
	 * @param   string  $description
	 */
	protected function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * Convert the configuration to a key-value array. The result can be fed to the setFromParameters() method to create
	 * the configuration object afresh. Therefore it can be used to save an Akeeba Replace job.
	 *
	 * @return  array
	 */
	public function toArray()
	{
		$ret = [];

		$refObject = new \ReflectionObject($this);

		foreach ($refObject->getProperties(\ReflectionProperty::IS_PRIVATE) as $refProp)
		{
			$propName = $refProp->getName();
			$methods = [
				'get' . ucfirst($propName),
				'is' . ucfirst($propName)
			];

			foreach ($methods as $method)
			{
				if (method_exists($this, $method))
				{
					break;
				}

				$method = '';
			}

			if (empty($method))
			{
				continue;
			}

			$ret[$propName] = $this->{$method}();
		}

		return $ret;
	}
}