<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Database\Driver;

use Akeeba\Replace\Database\Query;
use Akeeba\Replace\Database\QueryLimitable;
use Akeeba\Replace\Database\QueryPreparable;
use Akeeba\Replace\Database\Driver;

/**
 * PDO Database Driver Class
 *
 * @see    http://php.net/pdo
 * @since  1.0
 */
abstract class Pdo extends Driver
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $name = 'pdo';

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc.  The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $nameQuote = "'";

	/**
	 * The null or zero representation of a timestamp for the database driver.  This should be
	 * defined in child classes to hold the appropriate value for the engine.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $nullDate = '0000-00-00 00:00:00';

	/**
	 * The prepared statement.
	 *
	 * @var    resource
	 * @since  1.0
	 */
	protected $prepared;

	/**
	 * Contains the current query execution status
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $executed = false;

	/**
	 * How many levels deep our transaction is
	 *
	 * @var   int
	 */
	protected $transactionDepth = 0;

	public static $dbtech = 'pdo';

	/**
	 * Constructor.
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since   1.0
	 */
	public function __construct($options)
	{
		// Get some basic values from the options.
		$options['driver'] = (isset($options['driver'])) ? $options['driver'] : 'odbc';
		$options['dsn'] = (isset($options['dsn'])) ? $options['dsn'] : '';
		$options['host'] = (isset($options['host'])) ? $options['host'] : 'localhost';
		$options['database'] = (isset($options['database'])) ? $options['database'] : '';
		$options['user'] = (isset($options['user'])) ? $options['user'] : '';
		$options['password'] = (isset($options['password'])) ? $options['password'] : '';
		$options['driverOptions'] = (isset($options['driverOptions'])) ? $options['driverOptions'] : array();

		// Finalize initialisation
		parent::__construct($options);
	}

	/**
	 * Destructor.
	 *
	 * @since   1.0
	 */
	public function __destruct()
	{
		$this->freeResult();

		// If we are reusing another DB driver's connection we just remove the reference
		if (isset($this->options['connection']))
		{
			unset($this->options['connection']);

			$this->connection = null;

			return;
		}

		unset($this->connection);
	}

	/**
	 * Connects to the database if needed.
	 *
	 * @return  void  Returns void if the database connected successfully.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 * @throws  \UnexpectedValueException
	 */
	public function connect()
	{
		if (is_object($this->connection))
		{
			return;
		}

		// Was there an external connection originally passed to this object?
		if (isset($this->options['connection']) && !empty($this->options['connection']))
		{
			return;
		}

		// Make sure the PDO extension for PHP is installed and enabled.
		if (!static::isSupported())
		{
			throw new \RuntimeException('PDO Extension is not available.', 1);
		}

		// Find the correct PDO DSN Format to use:
		switch (strtolower($this->options['driver']))
		{
            // @codeCoverageIgnoreStart
			case 'cubrid':
				$this->options['port'] = (isset($this->options['port'])) ? $this->options['port'] : 33000;

				$format = 'cubrid:host=#HOST#;port=#PORT#;dbname=#DBNAME#';

				$replace = array('#HOST#', '#PORT#', '#DBNAME#');
				$with = array($this->options['host'], $this->options['port'], $this->options['database']);

				break;

			case 'dblib':
				$this->options['port'] = (isset($this->options['port'])) ? $this->options['port'] : 1433;

				$format = 'dblib:host=#HOST#;port=#PORT#;dbname=#DBNAME#';

				$replace = array('#HOST#', '#PORT#', '#DBNAME#');
				$with = array($this->options['host'], $this->options['port'], $this->options['database']);

				break;

			case 'firebird':
				$this->options['port'] = (isset($this->options['port'])) ? $this->options['port'] : 3050;

				$format = 'firebird:dbname=#DBNAME#';

				$replace = array('#DBNAME#');
				$with = array($this->options['database']);

				break;

			case 'ibm':
				$this->options['port'] = (isset($this->options['port'])) ? $this->options['port'] : 56789;

				if (!empty($this->options['dsn']))
				{
					$format = 'ibm:DSN=#DSN#';

					$replace = array('#DSN#');
					$with = array($this->options['dsn']);
				}
				else
				{
					$format = 'ibm:hostname=#HOST#;port=#PORT#;database=#DBNAME#';

					$replace = array('#HOST#', '#PORT#', '#DBNAME#');
					$with = array($this->options['host'], $this->options['port'], $this->options['database']);
				}

				break;

			case 'informix':
				$this->options['port'] = (isset($this->options['port'])) ? $this->options['port'] : 1526;
				$this->options['protocol'] = (isset($this->options['protocol'])) ? $this->options['protocol'] : 'onsoctcp';

				if (!empty($this->options['dsn']))
				{
					$format = 'informix:DSN=#DSN#';

					$replace = array('#DSN#');
					$with = array($this->options['dsn']);
				}
				else
				{
					$format = 'informix:host=#HOST#;service=#PORT#;database=#DBNAME#;server=#SERVER#;protocol=#PROTOCOL#';

					$replace = array('#HOST#', '#PORT#', '#DBNAME#', '#SERVER#', '#PROTOCOL#');
					$with = array($this->options['host'], $this->options['port'], $this->options['database'], $this->options['server'], $this->options['protocol']);
				}

				break;

			case 'mssql':
				$this->options['port'] = (isset($this->options['port'])) ? $this->options['port'] : 1433;

				$format = 'mssql:host=#HOST#;port=#PORT#;dbname=#DBNAME#';

				$replace = array('#HOST#', '#PORT#', '#DBNAME#');
				$with = array($this->options['host'], $this->options['port'], $this->options['database']);

				break;
            // @codeCoverageIgnoreEnd
			case 'mysql':
				$this->options['port'] = (isset($this->options['port'])) ? $this->options['port'] : 3306;

				$format = 'mysql:host=#HOST#;port=#PORT#;dbname=#DBNAME#;charset=#CHARSET#';

				$replace = array('#HOST#', '#PORT#', '#DBNAME#', '#CHARSET#');
				$with = array($this->options['host'], $this->options['port'], $this->options['database'], $this->options['charset']);

				break;
            // @codeCoverageIgnoreStart
			case 'oci':
				$this->options['port'] = (isset($this->options['port'])) ? $this->options['port'] : 1521;
				$this->options['charset'] = (isset($this->options['charset'])) ? $this->options['charset'] : 'AL32UTF8';

				if (!empty($this->options['dsn']))
				{
					$format = 'oci:dbname=#DSN#';

					$replace = array('#DSN#');
					$with = array($this->options['dsn']);
				}
				else
				{
					$format = 'oci:dbname=//#HOST#:#PORT#/#DBNAME#';

					$replace = array('#HOST#', '#PORT#', '#DBNAME#');
					$with = array($this->options['host'], $this->options['port'], $this->options['database']);
				}

				$format .= ';charset=' . $this->options['charset'];

				break;

			case 'odbc':
				$format = 'odbc:DSN=#DSN#;UID:#USER#;PWD=#PASSWORD#';

				$replace = array('#DSN#', '#USER#', '#PASSWORD#');
				$with = array($this->options['dsn'], $this->options['user'], $this->options['password']);

				break;

			case 'pgsql':
				$this->options['port'] = (isset($this->options['port'])) ? $this->options['port'] : 5432;

				$format = 'pgsql:host=#HOST#;port=#PORT#;dbname=#DBNAME#';

				$replace = array('#HOST#', '#PORT#', '#DBNAME#');
				$with = array($this->options['host'], $this->options['port'], $this->options['database']);

				break;

			case 'sqlite':
				if (isset($this->options['version']) && $this->options['version'] == 2)
				{
					$format = 'sqlite2:#DBNAME#';
				}
				else
				{
					$format = 'sqlite:#DBNAME#';
				}

				$replace = array('#DBNAME#');
				$with = array($this->options['database']);

				break;

			case 'sybase':
				$this->options['port'] = (isset($this->options['port'])) ? $this->options['port'] : 1433;

				$format = 'mssql:host=#HOST#;port=#PORT#;dbname=#DBNAME#';

				$replace = array('#HOST#', '#PORT#', '#DBNAME#');
				$with = array($this->options['host'], $this->options['port'], $this->options['database']);

				break;
            // @codeCoverageIgnoreEnd
			default:
				throw new \UnexpectedValueException('The ' . $this->options['driver'] . ' driver is not supported.');
		}

		// Create the connection string:
		$connectionString = str_replace($replace, $with, $format);

		try
		{
			$this->connection = new \PDO(
				$connectionString,
				$this->options['user'],
				$this->options['password'],
				$this->options['driverOptions']
			);
		}
		catch (\PDOException $e)
		{
			throw new \RuntimeException('Could not connect to PDO' . ': ' . $e->getMessage(), 2, $e);
		}
	}

	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function disconnect()
	{
		$this->freeResult();
		$this->connection = null;
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * Oracle escaping reference:
	 * http://www.orafaq.com/wiki/SQL_FAQ#How_does_one_escape_special_characters_when_writing_SQL_queries.3F
	 *
	 * SQLite escaping notes:
	 * http://www.sqlite.org/faq.html#q14
	 *
	 * Method body is as implemented by the Zend Framework
	 *
	 * Note: Using query objects with bound variables is
	 * preferable to the below.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Unused optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   1.0
	 */
	public function escape($text, $extra = false)
	{
		if (is_int($text) || is_float($text))
		{
			return $text;
		}

		if (is_null($text))
		{
			return 'NULL';
		}

		$text = str_replace("'", "''", $text);

		return addcslashes($text, "\000\n\r\\\032");
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   1.0
	 * @throws  \Exception
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		static $isReconnecting = false;

		$this->connect();

		if (!is_object($this->connection))
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

		// Execute the query.
		$this->executed = false;
		$executedSQL = '';

		if ($this->prepared instanceof \PDOStatement)
		{
			// Bind the variables:
			if ($this->sql instanceof QueryPreparable)
			{
				$bounded =& $this->sql->getBounded();

				foreach ($bounded as $key => $obj)
				{
					$this->prepared->bindParam($key, $obj->value, $obj->dataType, $obj->length, $obj->driverOptions);
				}
			}

			$executedSQL = $this->prepared->queryString;
			$this->executed = $this->prepared->execute();
		}

		// If an error occurred handle it.
		if (!$this->executed)
		{
			// Get the error number and message before we execute any more queries.
			$errorNum = (int) $this->connection->errorCode();
			$errorMsg = (string) 'SQL: ' . implode(", ", $this->connection->errorInfo());

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
				catch (\RuntimeException $e)
					// If connect fails, ignore that exception and throw the normal exception.
				{
					// Get the error number and message.
					$this->errorNum = (int) $this->connection->errorCode();
					$this->errorMsg = (string) 'SQL: ' . implode(", ", $this->connection->errorInfo());

					// Throw the normal query exception.
					throw new \RuntimeException($this->errorMsg, $this->errorNum);
				}

				// Since we were able to reconnect, run the query again.
				$result = $this->execute();
				$isReconnecting = false;

				return $result;
			}
			else
			// The server was not disconnected.
			{
				// Get the error number and message from before we tried to reconnect.
				$this->errorNum = $errorNum;
				$this->errorMsg = $errorMsg;

				// Throw the normal query exception.
				throw new \RuntimeException($this->errorMsg, $this->errorNum);
			}
		}

		if (substr(trim($executedSQL), 0, 123) == 'SELECT')
		{
			return $this->prepared;
		}

		return true;
	}

	/**
	 * Retrieve a PDO database connection attribute
	 * http://www.php.net/manual/en/pdo.getattribute.php
	 *
	 * Usage: $db->getOption(PDO::ATTR_CASE);
	 *
	 * @param   mixed  $key  One of the PDO::ATTR_* Constants
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function getOption($key)
	{
		$this->connect();

		return $this->connection->getAttribute($key);
	}

	/**
	 * Get a query to run and verify the database is operational.
	 *
	 * @return  string  The query to check the health of the DB.
	 *
	 * @since   1.0
	 */
	public function getConnectedQuery()
	{
		return 'SELECT 1';
	}

	/**
	 * Sets an attribute on the PDO database handle.
	 * http://www.php.net/manual/en/pdo.setattribute.php
	 *
	 * Usage: $db->setOption(PDO::ATTR_CASE, PDO::CASE_UPPER);
	 *
	 * @param   integer  $key    One of the PDO::ATTR_* Constants
	 * @param   mixed    $value  One of the associated PDO Constants
	 *                           related to the particular attribute
	 *                           key.
	 *
	 * @return boolean
	 *
	 * @since  1.0
	 */
	public function setOption($key, $value)
	{
		$this->connect();

		return $this->connection->setAttribute($key, $value);
	}

	/**
	 * Test to see if the PDO extension is available.
	 * Override as needed to check for specific PDO Drivers.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return defined('\\PDO::ATTR_DRIVER_NAME');
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 *
	 * @since   1.0
	 */
	public function connected()
	{
		if (is_null($this->connection))
		{
			return false;
		}

		// Flag to prevent recursion into this function.
		static $checkingConnected = false;

		if ($checkingConnected)
		{
			// Reset this flag and throw an exception.
			$checkingConnected = true;
			throw new \RuntimeException('Recursion trying to check if connected.', 500);
		}

		// Backup the query state.
		$sql = $this->sql;
		$limit = $this->limit;
		$offset = $this->offset;
		$prepared = $this->prepared;

		try
		{
			// Set the checking connection flag.
			$checkingConnected = true;

			// Run a simple query to check the connection.
			$this->setQuery($this->getConnectedQuery());
			$status = (bool) $this->loadResult();
		}
		catch (\Exception $e)
			// If we catch an exception here, we must not be connected.
		{
			$status = false;
		}

		// Restore the query state.
		$this->sql = $sql;
		$this->limit = $limit;
		$this->offset = $offset;
		$this->prepared = $prepared;
		$checkingConnected = false;

		return $status;
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 * Only applicable for DELETE, INSERT, or UPDATE statements.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 * @since   1.0
	 */
	public function getAffectedRows()
	{
		$this->connect();

		if ($this->prepared instanceof \PDOStatement)
		{
			return $this->prepared->rowCount();
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param   resource  $cursor  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 *
	 * @since   1.0
	 */
	public function getNumRows($cursor = null)
	{
		$this->connect();

		if ($cursor instanceof \PDOStatement)
		{
			return $cursor->rowCount();
		}
		elseif ($this->prepared instanceof \PDOStatement)
		{
			return $this->prepared->rowCount();
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  string  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   1.0
	 */
	public function insertid()
	{
		$this->connect();

		// Error suppress this to prevent PDO warning us that the driver doesn't support this operation.
		return @$this->connection->lastInsertId();
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function select($database)
	{
		$this->connect();

		$this->_database = $database;

		return true;
	}

	/**
	 * Sets the SQL statement string for later execution.
	 *
	 * @param   mixed    $query          The SQL statement to set either as a JDatabaseQuery object or a string.
	 * @param   integer  $offset         The affected row offset to set.
	 * @param   integer  $limit          The maximum affected rows to set.
	 * @param   array    $driverOptions  The optional PDO driver options
	 *
	 * @return  Pdo  This object to support method chaining.
	 *
	 * @since   1.0
	 */
	public function setQuery($query, $offset = null, $limit = null, $driverOptions = array())
	{
		$this->connect();

		$this->freeResult();

		if (is_string($query))
		{
			// Allows taking advantage of bound variables in a direct query:
			$query = $this->getQuery(true)->setQuery($query);
		}

		if ($query instanceof QueryLimitable && !is_null($offset) && !is_null($limit))
		{
			$query->setLimit($limit, $offset);
		}

		$sql = $this->replacePrefix((string) $query);

		$this->prepared = $this->connection->prepare($sql, $driverOptions);

		// Store reference to the DatabaseQuery instance:
		parent::setQuery($query, $offset, $limit);

		return $this;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	public function setUTF()
	{
		return false;
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, commit to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function transactionCommit($toSavepoint = false)
	{
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth == 1)
		{
			$this->connection->commit();
		}

		$this->transactionDepth--;
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, rollback to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function transactionRollback($toSavepoint = false)
	{
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth == 1)
		{
			$this->connection->rollBack();
		}

		$this->transactionDepth--;
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @param   boolean  $asSavepoint  If true and a transaction is already active, a savepoint will be created.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function transactionStart($asSavepoint = false)
	{
		$this->connect();

		if (!$asSavepoint || !$this->transactionDepth)
		{
			$this->connection->beginTransaction();
		}

		$this->transactionDepth++;
	}

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   1.0
	 */
	protected function fetchArray($cursor = null)
	{
		if (!empty($cursor) && $cursor instanceof \PDOStatement)
		{
			return $cursor->fetch(\PDO::FETCH_NUM);
		}

		if ($this->prepared instanceof \PDOStatement)
		{
			return $this->prepared->fetch(\PDO::FETCH_NUM);
		}
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   1.0
	 */
	public function fetchAssoc($cursor = null)
	{
		if (!empty($cursor) && $cursor instanceof \PDOStatement)
		{
			return $cursor->fetch(\PDO::FETCH_ASSOC);
		}

		if ($this->prepared instanceof \PDOStatement)
		{
			return $this->prepared->fetch(\PDO::FETCH_ASSOC);
		}
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   mixed   $cursor  The optional result set cursor from which to fetch the row.
	 * @param   string  $class   Unused, only necessary so method signature will be the same as parent.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   1.0
	 */
	public function fetchObject($cursor = null, $class = '\\stdClass')
	{
		if (!empty($cursor) && $cursor instanceof \PDOStatement)
		{
			return $cursor->fetchObject($class);
		}

		if ($this->prepared instanceof \PDOStatement)
		{
			return $this->prepared->fetchObject($class);
		}
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function freeResult($cursor = null)
	{
		$this->executed = false;

		if ($cursor instanceof \PDOStatement)
		{
			$cursor->closeCursor();
			$cursor = null;
		}

		if ($this->prepared instanceof \PDOStatement)
		{
			$this->prepared->closeCursor();
			$this->prepared = null;
		}
	}

	/**
	 * Method to get the next row in the result set from the database query as an array.
	 *
	 * @return  mixed  The result of the query as an array, false if there are no more rows.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function loadNextAssoc()
	{
		$this->connect();

		// Execute the query and get the result set cursor.
		if (!$this->executed)
		{
			if (!($this->execute()))
			{
				return $this->errorNum ? null : false;
			}
		}

		// Get the next row from the result set as an object of type $class.
		$row = $this->fetchAssoc();

		if ($row)
		{
			return $row;
		}

		// Free up system resources and return.
		$this->freeResult();

		return false;
	}

	/**
	 * PDO does not support serialize
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function __sleep()
	{
		$serializedProperties = array();

		$reflect = new \ReflectionClass($this);

		// Get properties of the current class
		$properties = $reflect->getProperties();

		foreach ($properties as $property)
		{
			// Do not serialize properties that are PDO
			if ($property->isStatic() == false && !($this->{$property->name} instanceof \PDO))
			{
				array_push($serializedProperties, $property->name);
			}
		}

		return $serializedProperties;
	}

	/**
	 * Wake up after serialization
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function __wakeup()
	{
		// Get connection back
		$this->__construct($this->options);
	}

    /**
     * Get the current query object or a new Query object.
     *
     * @param   boolean  $new  False to return the current query object, True to return a new Query object.
     *
     * @return  Query  The current query object or a new object extending the Query class.
     *
     * @throws  \RuntimeException
     */
    public function getQuery($new = false)
    {
        if ($new)
        {
            // We are going to use the generic PDO driver not matter what
            $class = '\\Akeeba\\Replace\\Database\\Query\\Pdo';

            // Make sure we have a query class for this driver.
            if (!class_exists($class))
            {
                // If it doesn't exist we are at an impasse so throw an exception.
                throw new \RuntimeException('Database Query Class not found.');
            }

            return new $class($this);
        }
        else
        {
            return $this->sql;
        }
    }
}
