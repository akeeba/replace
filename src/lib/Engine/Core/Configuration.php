<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Core;

/**
 * Configuration for Akeeba Replace's core
 *
 * @package Akeeba\Replace\Engine\Core
 */
class Configuration
{
	/**
	 * Output SQL file path. Empty = no SQL output
	 *
	 * @var  string
	 */
	private $outputSQLFile = '';

	/**
	 * Backup SQL file path. Empty = no backup
	 *
	 * @var  string
	 */
	private $backupSQLFile = '';

	/**
	 * Should I run actions directly to the database?
	 *
	 * @var  bool
	 */
	private $liveMode = true;

	/**
	 * List of per-database action class names to instantiate. Classes must implement DatabaseActionInterface.
	 *
	 * @var  string[]
	 */
	private $perDatabaseClasses = [];

	/**
	 * List of per-table action class names to instantiate. Classes must implement TableActionInterface.
	 *
	 * @var  string[]
	 */
	private $perTableClasses = [];

	/**
	 * List of per-row action class names to instantiate. Classes must implement RowActionInterface.
	 *
	 * @var  string[]
	 */
	private $perRowClasses = [];

	/**
	 * Include all tables, regardless of their prefix. False = only those matching the configured prefix.
	 *
	 * @var  bool
	 */
	private $allTables = false;

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
	public function setOutputSQLFile($outputSQLFile)
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
	public function setBackupSQLFile($backupSQLFile)
	{
		if (!is_string($backupSQLFile))
		{
			return $this;
		}

		$this->backupSQLFile = $backupSQLFile;

		return $this;
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
	public function setLiveMode($liveMode)
	{
		$liveMode = is_bool($liveMode) ? $liveMode : ($liveMode == 1);

		$this->liveMode = $liveMode;

		return $this;
	}

	/**
	 * Get the list of per-database action class names to instantiate.
	 *
	 * @return  string[]
	 *
	 * @codeCoverageIgnore
	 */
	public function getPerDatabaseClasses()
	{
		return $this->perDatabaseClasses;
	}

	/**
	 * Set the list of per-database action class names to instantiate. Classes must implement
	 * DatabaseActionInterface.
	 *
	 * @param   string[]  $perDatabaseClasses
	 *
	 * @return  Configuration
	 */
	public function setPerDatabaseClasses(array $perDatabaseClasses)
	{
		$this->perDatabaseClasses = [];

		foreach ($perDatabaseClasses as $class)
		{
			if (!class_exists($class))
			{
				continue;
			}

			$this->perDatabaseClasses[] = $class;
		}

		return $this;
	}

	/**
	 * Get the list of per-table action class names to instantiate.
	 *
	 * @return  string[]
	 *
	 * @codeCoverageIgnore
	 */
	public function getPerTableClasses()
	{
		return $this->perTableClasses;
	}

	/**
	 * Set the list of per-table action class names to instantiate. Classes must implement TableActionInterface.
	 *
	 * @param   string[]  $perTableClasses
	 *
	 * @return  Configuration
	 */
	public function setPerTableClasses(array $perTableClasses)
	{
		$this->perTableClasses = [];

		foreach ($perTableClasses as $class)
		{
			if (!class_exists($class))
			{
				continue;
			}

			$this->perTableClasses[] = $class;
		}

		return $this;
	}

	/**
	 * Get the list of per-row action class names to instantiate.
	 *
	 * @return  string[]
	 *
	 * @codeCoverageIgnore
	 */
	public function getPerRowClasses()
	{
		return $this->perRowClasses;
	}

	/**
	 * Set the list of per-row action class names to instantiate. Classes must implement RowActionInterface.
	 *
	 * @param   string[]  $perRowClasses
	 *
	 * @return  Configuration
	 */
	public function setPerRowClasses(array $perRowClasses)
	{
		$this->perRowClasses = [];

		foreach ($perRowClasses as $class)
		{
			if (!class_exists($class))
			{
				continue;
			}

			$this->perRowClasses[] = $class;
		}

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
	public function setAllTables($allTables)
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
	public function setExcludeTables(array $excludeTables)
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
	public function setExcludeRows(array $excludeRows)
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
	public function setRegularExpressions($regularExpressions)
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
	public function setReplacements(array $replacements)
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
	public function setDatabaseCollation($databaseCollation)
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
	public function setTableCollation($tableCollation)
	{
		if (!is_string($tableCollation))
		{
			return $this;
		}

		$this->tableCollation = $tableCollation;

		return $this;
	}

	/**
	 * Populates the Configuration from a key-value parameters array.
	 *
	 * @param   array  $params  A key-value array with the configuration variables.
	 *
	 * @return void
	 */
	public function setFromParameters(array $params)
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
}