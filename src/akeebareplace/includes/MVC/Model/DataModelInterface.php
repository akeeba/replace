<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\MVC\Model;

use Akeeba\Replace\Database\Metadata\Column;
use stdClass;
use wpdb;

/**
 * An interface to a Data-aware Model
 *
 * @package Akeeba\Replace\WordPress\MVC\Model
 */
interface DataModelInterface
{
	/**
	 * Data-aware model constructor.
	 *
	 * @param   wpdb  $db  The WordPress database object
	 */
	public function __construct(wpdb $db);

	/**
	 * Get the name of the database table this model implements
	 *
	 * @return  string
	 */
	public function getTableName();

	/**
	 * Get the name of the column which is our primary key
	 *
	 * @return  int
	 */
	public function getPKName();

	/**
	 * Return a list of items
	 *
	 * @param   bool  $overrideLimits  Ignore the limits and return all records
	 * @param   int   $limitstart      First record to return. 0 means "from the very start".
	 * @param   int   $limit           How many records to return.
	 *
	 * @return  array
	 */
	public function getItems($overrideLimits = false, $limitstart = 0, $limit = 0);

	/**
	 * Return a single database row given an ID. WARNING! Sanitize the ID before sending it here.
	 *
	 * @param   mixed  $id  The record ID
	 *
	 * @return  stdClass|bool  The record, or boolean false if loading it was not possible
	 */
	public function getItem($id);

	/**
	 * Return the total number of rows in the query
	 *
	 * @return  int
	 */
	public function getTotal();

	/**
	 * Builds the query for retrieving rows
	 *
	 * @return  string
	 */
	public function buildQuery();

	/**
	 * Builds the query for counting rows
	 *
	 * @return  string
	 */
	public function buildCountQuery();

	/**
	 * Return the reference to the WP database object known to this object
	 *
	 * @return  wpdb
	 */
	public function getDbo();

	/**
	 * Return the metadata of the table's columns, indexed by the column names
	 *
	 * @return  Column[]
	 */
	public function getColumnMeta();

	/**
	 * Save a row back to the database
	 *
	 * @param   array  $data  The data to save
	 *
	 * @return  mixed  The record ID affected, or boolean false if saving failed
	 */
	public function save(array $data);

	/**
	 * Delete a number of rows
	 *
	 * @param   array  $ids  The IDs of the records to delete
	 *
	 * @return  bool
	 */
	public function delete(array $ids = []);

	/**
	 * Returns a new record as a stdClass object, populated with the default values of the table columns.
	 *
	 * @return  stdClass
	 */
	public function getNewRecord();
}