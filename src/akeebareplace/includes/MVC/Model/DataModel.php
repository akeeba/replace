<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\MVC\Model;

use Akeeba\Replace\Database\Metadata\Column;
use Akeeba\Replace\WordPress\Helper\WordPress;
use LogicException;
use stdClass;
use wpdb;

/**
 * Abstract class for a data-aware Model
 *
 * @package Akeeba\Replace\WordPress\MVC\Model
 */
abstract class DataModel extends Model implements DataModelInterface
{
	/**
	 * Reference to the WordPress database object
	 *
	 * @var  wpdb
	 */
	private $db;

	/**
	 * The name of the database table we are connected to.
	 *
	 * @var  string
	 */
	protected $tableName;

	/**
	 * The name of the primary key column of the table we are connected to.
	 *
	 * @var  string
	 */
	protected $pkName;

	/**
	 * Column metadata, keyed by table
	 *
	 * @var  array
	 */
	private static $columnMeta = [];

	/**
	 * Return an instance of a Model by name.
	 *
	 * @param   string  $name      The name of the Model to return
	 *
	 * @return  DataModelInterface
	 */
	public static function getInstance($name)
	{
		$model = Model::getInstance($name);

		if ($model instanceof DataModelInterface)
		{
			/** @var DataModelInterface $model */
			return $model;
		}

		throw new \InvalidArgumentException(sprintf("The Model $name does not implement the DataModelInterface", $name));
	}

	/**
	 * Data-aware model constructor.
	 *
	 * @param   wpdb  $db  The WordPress database object
	 */
	public function __construct(wpdb $db)
	{
		$this->db = $db;

		if (empty($this->tableName))
		{
			throw new LogicException(sprintf("DataModel class %s does not have a table name set.", get_class($this)));
		}

		if (empty($this->pkName))
		{
			throw new LogicException(sprintf("DataModel class %s does not have a Primary Key name set.", get_class($this)));
		}

		$this->getColumnMeta();
	}

	/**
	 * Get the name of the database table this model implements
	 *
	 * @return  string
	 */
	public function getTableName()
	{
		return $this->tableName;
	}

	/**
	 * Get the name of the column which is our primary key
	 *
	 * @return  int
	 */
	public function getPKName()
	{
		return $this->pkName;
	}

	/**
	 * Return the reference to the WP database object known to this object
	 *
	 * @return  wpdb
	 */
	public function getDbo()
	{
		return $this->db;
	}

	/**
	 * Return the metadata of the table's columns, indexed by the column names
	 *
	 * @return  Column[]
	 */
	public function getColumnMeta()
	{
		if (!isset(self::$columnMeta[$this->tableName]))
		{
			$query      = "SHOW FULL COLUMNS FROM `{$this->tableName}`";
			$allColumns = $this->db->get_results($query, ARRAY_A);

			$columnNames = array_map(function ($columnInfo) {
				return $columnInfo['Field'];
			}, $allColumns);
			$columnMeta = array_map(function ($columnInfo) {
				return Column::fromDatabaseResult($columnInfo);
			}, $allColumns);

			self::$columnMeta[$this->tableName] = array_combine($columnNames, $columnMeta);
		}

		return self::$columnMeta[$this->tableName];
	}

	/**
	 * Return a list of items
	 *
	 * @param   bool  $overrideLimits  Ignore the limits and return all records
	 * @param   int   $limitstart      First record to return. 0 means "from the very start".
	 * @param   int   $limit           How many records to return.
	 *
	 * @return  array
	 */
	public function getItems($overrideLimits = false, $limitstart = 0, $limit = 0)
	{
		if ((int) $limitstart <= 0)
		{
			$limitstart = (int) filter_input(INPUT_GET, 'limitstart', FILTER_SANITIZE_NUMBER_INT);
		}

		if ((int) $limit <= 0)
		{
			$limit = (int) filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_NUMBER_INT);
		}

		if ((int) $limit <= 0)
		{
			$limit = WordPress::get_page_limit();
		}

		$query = $this->buildQuery();

		if (is_null($query))
		{
			return array();
		}

		if (!$overrideLimits)
		{
			$query .= sprintf(' LIMIT %d, %d', $limitstart, $limit);
		}

		$rows = $this->db->get_results($query, OBJECT);

		if (method_exists($this, 'onAfterGetItems'))
		{
			$this->onAfterGetItems($rows);
		}

		return $rows;
	}

	/**
	 * Return a single database row given an ID. WARNING! Sanitize the ID before sending it here.
	 *
	 * @param   mixed  $id  The record ID
	 *
	 * @return  stdClass|bool  The record, or boolean false if loading it was not possible
	 */
	public function getItem($id)
	{
		$protoQuery = "SELECT * FROM `{$this->tableName}` WHERE `{$this->pkName}` = %s";
		$query = $this->db->prepare($protoQuery, [$id]);

		return $this->db->get_row($query, OBJECT);
	}

	/**
	 * Return the total number of rows in the query
	 *
	 * @return  int
	 */
	public function getTotal()
	{
		return $this->db->get_var($this->buildCountQuery());
	}

	/**
	 * Builds the query for retrieving rows
	 *
	 * @return  string
	 */
	public function buildQuery()
	{
		return "SELECT * FROM `{$this->tableName}`";
	}

	/**
	 * Builds the query for counting rows
	 *
	 * @return  string
	 */
	public function buildCountQuery()
	{
		$query = $this->buildQuery();

		if (strtoupper(substr($query, 0, 9)) == 'SELECT * ')
		{
			return 'SELECT COUNT(*) ' . substr($query, 9);
		}

		return "SELECT COUNT(*) FROM ($query) AS `akeeba_temp_query_table`";
	}

	/**
	 * Save a row back to the database
	 *
	 * @param   array  $data  The data to save
	 *
	 * @return  mixed  The record ID affected, or boolean false if saving failed
	 */
	public function save(array $data)
	{
		$data = $this->filterDataColumns($data);

		$rowsAffected = $this->db->replace($this->tableName, $data);

		if ($rowsAffected === false)
		{
			return false;
		}

		return $this->db->insert_id;
	}

	/**
	 * Delete a number of rows
	 *
	 * @param   array  $ids  The IDs of the records to delete
	 *
	 * @return  bool
	 */
	public function delete(array $ids = [])
	{
		$ids = $this->filterIDs($ids);

		if (empty($ids))
		{
			return false;
		}

		foreach ($ids as $id)
		{
			$rowsAffected = $this->db->delete($this->tableName, [$this->pkName => $id]);

			if ($rowsAffected === false)
			{
				return false;
			}

			// Call the "onAfterDelete" on each record
			if (method_exists($this, 'onAfterDelete'))
			{
				$this->onAfterDelete($id);
			}
		}

		return true;
	}

	/**
	 * Gets a data array and only keeps the items whose keys correspond to columns of the database table. If there are
	 * any missing columns they are filled in with their default value.
	 *
	 * This DOES NOT sanitize or validate the data. You have to do it manually.
	 *
	 * @param   array   $data  The data to filter.
	 *
	 * @return  array  The filtered data.
	 */
	protected function filterDataColumns(array $data)
	{
		$columnMeta  = $this->getColumnMeta();
		$columnNames = array_keys($columnMeta);

		$newData = [];

		foreach ($data as $k => $v)
		{
			if (in_array($k, $columnNames))
			{
				continue;
			}

			$newData[$k] = $v;
		}

		foreach ($columnNames as $k)
		{
			if (array_key_exists($k, $newData))
			{
				continue;
			}

			$newData[$k] = $columnMeta[$k]->getDefault();
		}

		return $newData;
	}

	/**
	 * Filters an array of IDs keeping only the unique integer values of its non-empty items.
	 *
	 * @param   array  $ids  The IDs to filter
	 *
	 * @return  array  The filtered IDs
	 */
	protected function filterIDs(array $ids)
	{
		// Quick exit for empty arrays
		if (empty($ids))
		{
			return [];
		}

		// Convert everything to an integer or a null
		$ids = array_map(function ($v) {
			if (is_null($v))
			{
				return null;
			}

			if (is_string($v))
			{
				$v = trim($v);
			}

			if ($v === "")
			{
				return null;
			}

			return (int) $v;
		}, $ids);
		// Remove duplicate values
		$ids = array_unique($ids);
		// Remove nulls
		$ids = array_filter($ids, function ($v) {
			return !is_null($v);
		});

		return $ids;
	}
}
