<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Database\Driver;

/**
 * MySQL database driver
 *
 * @see         http://dev.mysql.com/doc/
 */
class Mysql extends Mysqli
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 */
	public $name = 'mysql';

	/**
	 * @var    string  The database technology family supported, e.g. mysql, mssql
	 */
	public static $dbtech = 'mysql';

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Array of database options with keys: host, user, password, database, select.
	 *
	 */
	public function __construct($options)
	{
		// Get some basic values from the options.
		$options['host'] = (isset($options['host'])) ? $options['host'] : 'localhost';
		$options['user'] = (isset($options['user'])) ? $options['user'] : 'root';
		$options['password'] = (isset($options['password'])) ? $options['password'] : '';
		$options['database'] = (isset($options['database'])) ? $options['database'] : '';
		$options['select'] = (isset($options['select'])) ? (bool) $options['select'] : true;

		// Finalize initialisation.
		parent::__construct($options);
	}

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		// If we are reusing another DB driver's connection we just remove the reference
		if (isset($this->options['connection']))
		{
			unset($this->options['connection']);

			$this->connection = null;

			return;
		}

		if (is_resource($this->connection))
		{
			mysql_close($this->connection);
		}
	}

	/**
	 * Connects to the database if needed.
	 *
	 * @return  void  Returns void if the database connected successfully.
	 *
	 * @throws  \RuntimeException
	 */
	public function connect()
	{
		if ($this->connection)
		{
			return;
		}

		// Was there an external connection originally passed to this object?
		if (isset($this->options['connection']) && !empty($this->options['connection']))
		{
			return;
		}

		// Make sure the MySQL extension for PHP is installed and enabled.
		if (!function_exists('mysql_connect'))
		{
			throw new \RuntimeException('Could not connect to MySQL.');
		}

		// Attempt to connect to the server.
		if (!($this->connection = @ mysql_connect($this->options['host'], $this->options['user'], $this->options['password'], true)))
		{
			throw new \RuntimeException('Could not connect to MySQL.');
		}

		// Set sql_mode to non_strict mode
		mysql_query("SET @@SESSION.sql_mode = '';", $this->connection);

		// If auto-select is enabled select the given database.
		if ($this->options['select'] && !empty($this->options['database']))
		{
			$this->select($this->options['database']);
		}

		// Set charactersets (needed for MySQL 4.1.2+).
		$this->setUTF();
	}

	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 *
	 */
	public function disconnect()
	{
		// Close the connection.
		mysql_close($this->connection);

		$this->connection = null;
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 */
	public function escape($text, $extra = false)
	{
		$this->connect();

		if (is_null($text))
		{
			return 'NULL';
		}

		$result = mysql_real_escape_string($text, $this->getConnection());

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

	/**
	 * Test to see if the MySQL connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 */
	public static function isSupported()
	{
		return (function_exists('mysql_connect'));
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 *
	 */
	public function connected()
	{
		if (is_resource($this->connection))
		{
			return @mysql_ping($this->connection);
		}

		return false;
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 */
	public function getAffectedRows()
	{
		$this->connect();

		return mysql_affected_rows($this->connection);
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param   resource  $cursor  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 *
	 */
	public function getNumRows($cursor = null)
	{
		$this->connect();

		return mysql_num_rows($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 *
	 */
	public function getVersion()
	{
		$this->connect();

		return mysql_get_server_info($this->connection);
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 *
	 */
	public function insertid()
	{
		$this->connect();

		return mysql_insert_id($this->connection);
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		static $isReconnecting = false;

		$this->connect();

		if (!is_resource($this->connection))
		{
			throw new \RuntimeException($this->errorMsg, $this->errorNum);
		}

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->replacePrefix((string) $this->sql);
		if ($this->limit > 0 || $this->offset > 0)
		{
			$sql .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
		}

		// Increment the query counter.
		$this->count++;

		// If debugging is enabled then let's log the query.
		if ($this->debug)
		{
			// Add the query to the object queue.
			$this->log[] = $sql;
		}

		// Reset the error values.
		$this->errorNum = 0;
		$this->errorMsg = '';

		// Execute the query. Error suppression is used here to prevent warnings/notices that the connection has been lost.
		$this->cursor = @mysql_query($sql, $this->connection);

		// If an error occurred handle it.
		if (!$this->cursor)
		{
			// Check if the server was disconnected.
			if (!$this->connected() && !$isReconnecting)
			{
				$isReconnecting = true;

				try
				{
					// Attempt to reconnect.
					$this->connection = null;
					$this->connect();
				}
				// If connect fails, ignore that exception and throw the normal exception.
				catch (\RuntimeException $e)
				{
					// Get the error number and message.
					$this->errorNum = (int) mysql_errno($this->connection);
					$this->errorMsg = (string) mysql_error($this->connection) . ' SQL=' . $sql;

					// Throw the normal query exception.
					throw new \RuntimeException($this->errorMsg, $this->errorNum);
				}

				// Since we were able to reconnect, run the query again.
				$result = $this->execute();
				$isReconnecting = false;

				return $result;
			}
			// The server was not disconnected.
			else
			{
				// Get the error number and message.
				$this->errorNum = (int) mysql_errno($this->connection);
				$this->errorMsg = (string) mysql_error($this->connection) . ' SQL=' . $sql;

				// Throw the normal query exception.
				throw new \RuntimeException($this->errorMsg, $this->errorNum);
			}
		}

		unset($sql);

		return $this->cursor;
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @throws  \RuntimeException
	 */
	public function select($database)
	{
		$this->connect();

		if (!$database)
		{
			return false;
		}

		if (!mysql_select_db($database, $this->connection))
		{
			throw new \RuntimeException('Could not connect to database.');
		}

		$this->_database = $database;

		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 *
	 */
	public function setUTF()
	{
		$this->connect();

		return mysql_set_charset('utf8', $this->connection);
	}

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 */
	protected function fetchArray($cursor = null)
	{
		return mysql_fetch_row($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 */
	public function fetchAssoc($cursor = null)
	{
		return mysql_fetch_assoc($cursor ? $cursor : $this->cursor);
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   mixed   $cursor  The optional result set cursor from which to fetch the row.
	 * @param   string  $class   The class name to use for the returned row object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 */
	public function fetchObject($cursor = null, $class = 'stdClass')
	{
		return mysql_fetch_object($cursor ? $cursor : $this->cursor, $class);
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 *
	 */
	public function freeResult($cursor = null)
	{
		mysql_free_result($cursor ? $cursor : $this->cursor);
	}
}
