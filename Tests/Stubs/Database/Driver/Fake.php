<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Database\Driver;

use Akeeba\Replace\Database\Driver;

/**
 * Fake database driver. Does absolutely nothing of substance.
 *
 * @package Akeeba\Replace\Test\Stubs\Database
 */
class Fake extends Driver
{

	public $name = 'fake';

	protected $nameQuote = '[]';

	protected $nullDate = '1BC';

	protected static $dbMinimum = '12.1';

	public function connect()
	{
		return true;
	}

	public function connected()
	{
		return true;
	}

	public function disconnect()
	{
		return;
	}

	public function dropTable($table, $ifExists = true)
	{
		return $this;
	}

	public function escape($text, $extra = false)
	{
		return $extra ? "/$text//" : "-$text-";
	}

	protected function fetchArray($cursor = null)
	{
		return array();
	}

	protected function fetchAssoc($cursor = null)
	{
		return array();
	}

	protected function fetchObject($cursor = null, $class = 'stdClass')
	{
		return new $class;
	}

	protected function freeResult($cursor = null)
	{
		return null;
	}

	public function getAffectedRows()
	{
		return 0;
	}

	public function getCollation()
	{
		return false;
	}

	public function getNumRows($cursor = null)
	{
		return 0;
	}

	public function getQuery($new = false)
	{
		return null;
	}

	public function getTableColumns($table, $typeOnly = true)
	{
		return array();
	}

	public function getTableCreate($tables)
	{
		return '';
	}

	public function getTableKeys($tables)
	{
		return array();
	}

	public function getTableList()
	{
		return array();
	}

	public function getVersion()
	{
		return '12.1';
	}

	public function insertid()
	{
		return 0;
	}

	public function lockTable($tableName)
	{
		return $this;
	}

	public function execute()
	{
		return false;
	}

	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
		return $this;
	}

	public function select($database)
	{
		return false;
	}

	public function setUTF()
	{
		return false;
	}

	public static function isSupported()
	{
		return true;
	}

	public function transactionCommit($toSavepoint = false)
	{
	}

	public function transactionRollback($toSavepoint = false)
	{
	}

	public function transactionStart($asSavepoint = false)
	{
	}

	public function unlockTables()
	{
		return $this;
	}
}
