<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Database\Driver;

use Akeeba\Replace\Database\Driver\Mysqli;
use Akeeba\Replace\Tests\Database\DriverTestCase;

class MysqliTest extends DriverTestCase
{
	/**
	 * @var   string  The name of the database driver to instantiate
	 */
	protected static $driverName = 'mysqli';

	/**
	 * Test connected method.
	 *
	 * @return  void
	 */
	public function testConnected()
	{
		$driver = self::getDriver();

		$driver->connect();
		$this->assertTrue($driver->connected(), 'Driver must report itself connected after successful connection');

		$driver->disconnect();
		$this->assertFalse($driver->connected(), 'Driver must report itself disconnected after successful disconnection');
	}

	/**
	 * Tests the dropTable method.
	 *
	 * @return  void
	 */
	public function testDropTable()
	{
		$driver = self::getDriver();

		// Make sure dropping a table (if it exists) works
		$this->assertThat(
			$driver->dropTable('#__bar', true),
			$this->isInstanceOf('\\Akeeba\\Replace\\Database\\Driver\\Mysqli'),
			'The table is dropped if present.'
		);

		// Make sure dropping a table which doesn't exist errors out with an exception
		$this->expectExceptionMessage('SQL=DROP TABLE `akr_bar`');
		$driver->dropTable('#__bar', false);
	}

	/**
	 * Tests the escape method.
	 *
	 * @param   string  $text     The string to be escaped.
	 * @param   boolean $extra    Optional parameter to provide extra escaping.
	 * @param   string  $expected The expected result.
	 *
	 * @return  void
	 *
	 * @dataProvider  \Akeeba\Replace\Tests\Database\MySQLProvider::testEscapeProvider()
	 */
	public function testEscape($text, $extra, $expected)
	{
		$driver = self::getDriver();

		$this->assertEquals($expected, $driver->escape($text, $extra));
	}

	/**
	 * Test getAffectedRows method.
	 *
	 * @return  void
	 */
	public function testGetAffectedRows()
	{
		$driver = self::getDriver();

		$query = $driver->getQuery(true);
		$query->delete();
		$query->from('#__dbtest');

		$driver->setQuery($query);
		$driver->execute();

		$this->assertEquals(4, $driver->getAffectedRows());
	}

	/**
	 * Test getCollation method.
	 *
	 * @return  void
	 */
	public function testGetCollation()
	{
		$driver = self::getDriver();

		$this->assertEquals('utf8mb4_unicode_520_ci', $driver->getCollation(), 'The getCollation method should return the collation of the database, not the first created table.');
	}

	/**
	 * Test getNumRows method.
	 *
	 * @return  void
	 */
	public function testGetNumRows()
	{
		$driver = self::getDriver();

		$query = $driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->where('description = ' . $driver->quote('one'));
		$driver->setQuery($query);

		$res = $driver->execute();

		$this->assertEquals(2, $driver->getNumRows($res));
	}

	/**
	 * Tests the getTableCreate method.
	 *
	 * @return  void
	 */
	public function testGetTableCreate()
	{
		$driver = self::getDriver();
		$create = $driver->getTableCreate('#__dbtest');

		$this->assertInternalType('array', $create, 'The statement to create the table is returned in an array.');
		$this->assertCount(1, $create, 'Only one element must be present in the CREATE TABLE results array');
		$this->assertArrayHasKey('#__dbtest', $create, 'The CREATE TABLE array must be keyed against the table name.');
		$this->assertContains('CREATE TABLE `akr_dbtest`', $create['#__dbtest'], 'The CREATE TABLE result must contain DDL for the requested table.');
	}

	/**
	 * Test getTableColumns method.
	 *
	 * @return  void
	 */
	public function testGetTableColumns()
	{
		$driver = self::getDriver();
		$tableCol = array('id' => 'int unsigned', 'title' => 'varchar', 'start_date' => 'datetime', 'description' => 'varchar');

		$this->assertEquals(
			$tableCol,
			$driver->getTableColumns('#__dbtest'),
			'The columns list must be accurate'
		);

		/* not only type field */
		$id = new \stdClass;
		$id->Default = null;
		$id->Field = 'id';
		$id->Type = 'int(10) unsigned';
		$id->Null = 'NO';
		$id->Key = 'PRI';
		$id->Collation = null;
		$id->Extra = 'auto_increment';
		$id->Privileges = 'select,insert,update,references';
		$id->Comment = '';

		$title = new \stdClass;
		$title->Default = null;
		$title->Field = 'title';
		$title->Type = 'varchar(50)';
		$title->Null = 'NO';
		$title->Key = '';
		$title->Collation = 'utf8_general_ci';
		$title->Extra = '';
		$title->Privileges = 'select,insert,update,references';
		$title->Comment = '';

		$start_date = new \stdClass;
		$start_date->Default = null;
		$start_date->Field = 'start_date';
		$start_date->Type = 'datetime';
		$start_date->Null = 'NO';
		$start_date->Key = '';
		$start_date->Collation = null;
		$start_date->Extra = '';
		$start_date->Privileges = 'select,insert,update,references';
		$start_date->Comment = '';

		$description = new \stdClass;
		$description->Default = null;
		$description->Field = 'description';
		$description->Type = 'varchar(255)';
		$description->Null = 'NO';
		$description->Key = '';
		$description->Collation = 'utf8_general_ci';
		$description->Extra = '';
		$description->Privileges = 'select,insert,update,references';
		$description->Comment = '';

		$this->assertEquals(				array(
				'id'          => $id,
				'title'       => $title,
				'start_date'  => $start_date,
				'description' => $description
			),
			$driver->getTableColumns('#__dbtest', false),
			'The column metadata must be accurate'
		);
	}

	/**
	 * Tests the getTableKeys method.
	 *
	 * @return  void
	 */
	public function testGetTableKeys()
	{
		$driver = self::getDriver();

		$tableKeys = $driver->getTableKeys('#__dbtest');
		$expected  = array(
			0 =>
				(object) array(
					'Table'         => 'akr_dbtest',
					'Non_unique'    => '0',
					'Key_name'      => 'PRIMARY',
					'Seq_in_index'  => '1',
					'Column_name'   => 'id',
					'Collation'     => null,
					'Cardinality'   => '4',
					'Sub_part'      => null,
					'Packed'        => null,
					'Null'          => '',
					'Index_type'    => 'HASH',
					'Comment'       => '',
					'Index_comment' => '',
				),
		);

		$this->assertInternalType('array', $tableKeys, 'The list of table keys for the table is returned in an array.');
		$this->assertCount(1, $tableKeys, 'The number of table keys returned must be accurate.');
		$this->assertEquals($expected, $tableKeys, 'The metadata of table keys returned must be accurate.');
	}

	/**
	 * Tests the getTableList method.
	 *
	 * @return  void
	 */
	public function testGetTableList()
	{
		$driver = self::getDriver();
		$tableList  = $driver->getTableList();

		$this->assertInternalType('array', $tableList, 'The list of tables for the database is returned in an array.');
		$this->assertArraySubset(array (
			'akr_dbtest',
			'akr_dbtest_innodb',
		), $tableList, 'The list of tables contains at least the tables we have already created.');
	}

	/**
	 * Test getVersion method.
	 *
	 * @return  void
	 */
	public function testGetVersion()
	{
		$driver = self::getDriver();
		$version = $driver->getVersion();
		$this->assertGreaterThan(
			0,
			strlen($version),
			'The getVersion method should return something without error.'
		);
		$this->assertTrue(version_compare($version, '5.0.0', 'ge'), 'The returned version must look like a MySQL / MariaDB version number of a supported MySQL-class database server.');
	}

	/**
	 * Test insertid method.
	 *
	 * @return  void
	 */
	public function testInsertid()
	{
		$driver = self::getDriver();
		$driver->truncateTable('#__dbtest');

		$query = $driver->getQuery(true)
			->insert('#__dbtest')
			->columns(array('title', 'start_date', 'description'))
			->values($driver->q('New record') . ', ' . $driver->q('2014-06-18 00:00:00') . ', ' . $driver->q('Something something something text'));
		$driver->setQuery($query)->execute();

		$insertId = $driver->insertid();

		$this->assertEquals(1, $insertId);
	}

	/**
	 * Test loadAssoc method.
	 *
	 * @return  void
	 */
	public function testLoadAssoc()
	{
		$driver = self::getDriver();
		$query = $driver->getQuery(true);
		$query->select('title');
		$query->from('#__dbtest');
		$driver->setQuery($query);
		$result = $driver->loadAssoc();

		$this->assertEquals(array('title' => 'Testing'), $result);
	}

	/**
	 * Test loadAssocList method.
	 *
	 * @return  void
	 */
	public function testLoadAssocList()
	{
		$driver = self::getDriver();
		$query = $driver->getQuery(true);
		$query->select('title');
		$query->from('#__dbtest');
		$driver->setQuery($query);
		$result = $driver->loadAssocList();

		$this->assertEquals(
			array(
				array('title' => 'Testing'),
				array('title' => 'Testing2'),
				array('title' => 'Testing3'),
				array('title' => 'Testing4')
			),
			$result
		);
	}

	/**
	 * Test loadColumn method
	 *
	 * @return  void
	 */
	public function testLoadColumn()
	{
		$driver = self::getDriver();
		$query = $driver->getQuery(true);
		$query->select('title');
		$query->from('#__dbtest');
		$driver->setQuery($query);
		$result = $driver->loadColumn();

		$this->assertEquals(array('Testing', 'Testing2', 'Testing3', 'Testing4'), $result);
	}

	/**
	 * Test loadObject method
	 *
	 * @return  void
	 */
	public function testLoadObject()
	{
		$driver = self::getDriver();
		$query = $driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->where('description=' . $driver->quote('three'));
		$driver->setQuery($query);
		$result = $driver->loadObject();

		$objCompare = new \stdClass;
		$objCompare->id = 3;
		$objCompare->title = 'Testing3';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'three';

		$this->assertEquals($objCompare, $result);
	}

	/**
	 * Test loadObjectList method
	 *
	 * @return  void
	 */
	public function testLoadObjectList()
	{
		$driver = self::getDriver();
		$query = $driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->order('id');
		$driver->setQuery($query);
		$result = $driver->loadObjectList();

		$expected = array();

		$objCompare = new \stdClass;
		$objCompare->id = 1;
		$objCompare->title = 'Testing';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'one';

		$expected[] = clone $objCompare;

		$objCompare = new \stdClass;
		$objCompare->id = 2;
		$objCompare->title = 'Testing2';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'one';

		$expected[] = clone $objCompare;

		$objCompare = new \stdClass;
		$objCompare->id = 3;
		$objCompare->title = 'Testing3';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'three';

		$expected[] = clone $objCompare;

		$objCompare = new \stdClass;
		$objCompare->id = 4;
		$objCompare->title = 'Testing4';
		$objCompare->start_date = '1980-04-18 00:00:00';
		$objCompare->description = 'four';

		$expected[] = clone $objCompare;

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test loadResult method
	 *
	 * @return  void
	 */
	public function testLoadResult()
	{
		$driver = self::getDriver();
		$query = $driver->getQuery(true);
		$query->select('id');
		$query->from('#__dbtest');
		$query->where('title=' . $driver->quote('Testing2'));

		$driver->setQuery($query);
		$result = $driver->loadResult();

		$this->assertEquals(2, $result);
	}

	/**
	 * Test loadRow method
	 *
	 * @return  void
	 */
	public function testLoadRow()
	{
		$driver = self::getDriver();
		$query = $driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->where('description=' . $driver->quote('three'));
		$driver->setQuery($query);
		$result = $driver->loadRow();

		$expected = array(3, 'Testing3', '1980-04-18 00:00:00', 'three');

		$this->assertEquals($expected, $result);
	}

	/**
	 * Test loadRowList method
	 *
	 * @return  void
	 */
	public function testLoadRowList()
	{
		$driver = self::getDriver();
		$query = $driver->getQuery(true);
		$query->select('*');
		$query->from('#__dbtest');
		$query->where('description=' . $driver->quote('one'));
		$driver->setQuery($query);
		$result = $driver->loadRowList();

		$expected = array(array(1, 'Testing', '1980-04-18 00:00:00', 'one'), array(2, 'Testing2', '1980-04-18 00:00:00', 'one'));

		$this->assertThat($result, $this->equalTo($expected), __LINE__);
	}

	/**
	 * Tests the renameTable method.
	 *
	 * @return  void
	 */
	public function testRenameTable()
	{
		$driver = self::getDriver();
		$newTableName = 'bak_renamed';

		// Drop the cloned table if it already exists
		$driver->dropTable('#__for_rename', true);

		// Create a clone of the test table
		$tablesDDL = $driver->getTableCreate([
			'#__dbtest'
		]);
		$sql = str_replace('akr_dbtest', '#__for_rename', $tablesDDL['#__dbtest']);
		$driver->setQuery($sql)->execute();

		// Rename the cloned table
		$driver->renameTable('#__for_rename', $newTableName);

		// Check name change
		$tableList = $driver->getTableList();
		$this->assertThat(in_array($newTableName, $tableList), $this->isTrue());

		// Restore initial state
		$driver->dropTable($newTableName, true);
	}

	/**
	 * Test the execute method
	 *
	 * @return  void
	 */
	public function testExecute()
	{
		$driver = self::getDriver();
		$driver->setQuery("REPLACE INTO `#__dbtest` SET `id` = 5, `title` = 'testTitle'");

		$this->assertTrue($driver->execute());

		$this->assertEquals(5, $driver->insertid());
	}

	/**
	 * Test select method.
	 *
	 * @return  void
	 */
	public function testSelect()
	{
		$driver = self::getDriver();

		$this->expectExceptionMessage('Could not connect to database.');
		$driver->select('DOES_NOT_EXIST');
	}

	/**
	 * Test setUTF method.
	 *
	 * @return  void
	 */
	public function testSetUTF()
	{
		$driver = self::getDriver();
		$driver->setUTF();

		$query = "show variables like 'character_set_client'";
		$currentCharset = $driver->setQuery($query)->loadColumn(1);

		$this->assertEquals('utf8', $currentCharset[0]);
	}

	/**
	 * Tests the transactionCommit method.
	 *
	 * @return  void
	 */
	public function testTransactionCommit()
	{
		$driver = self::getDriver();
		$driver->transactionStart();
		$queryIns = $driver->getQuery(true);
		$queryIns->insert('#__dbtest')
			->columns('id, title, start_date, description')
			->values("6, 'testTitle', '1970-01-01', 'testDescription'");

		$driver->setQuery($queryIns)->execute();

		$driver->transactionCommit();

		/* check if value is present */
		$queryCheck = $driver->getQuery(true);
		$queryCheck->select('*')
			->from('#__dbtest')
			->where('id = 6');
		$driver->setQuery($queryCheck);
		$result = $driver->loadRow();

		$expected = array('6', 'testTitle', '1970-01-01 00:00:00', 'testDescription');

		$this->assertEquals($expected, $result);
	}

	/**
	 * Tests the transactionRollback method, with and without savepoint.
	 *
	 * @param   string $toSavepoint Savepoint name to rollback transaction to
	 * @param   int    $tupleCount  Number of tuple found after insertion and rollback
	 *
	 * @return  void
	 *
	 * @dataProvider \Akeeba\Replace\Tests\Database\MySQLProvider::testTransactionRollbackProvider()
	 */
	public function testTransactionRollback($toSavepoint, $tupleCount)
	{
		$driver = self::getDriver();
		$driver->transactionStart();

		/* try to insert this tuple, inserted only when savepoint != null */
		$queryIns = $driver->getQuery(true);
		$queryIns->insert('#__dbtest_innodb')
			->columns('id, title, start_date, description')
			->values("7, 'testRollback', '1970-01-01', 'testRollbackSp'");
		$driver->setQuery($queryIns)->execute();

		/* create savepoint only if is passed by data provider */
		if (!is_null($toSavepoint))
		{
			$driver->transactionStart((boolean)$toSavepoint);
		}

		/* try to insert this tuple, always rolled back */
		$queryIns = $driver->getQuery(true);
		$queryIns->insert('#__dbtest_innodb')
			->columns('id, title, start_date, description')
			->values("8, 'testRollback', '1972-01-01', 'testRollbackSp'");
		$driver->setQuery($queryIns)->execute();

		$driver->transactionRollback((boolean)$toSavepoint);

		/* release savepoint and commit only if a savepoint exists */
		if (!is_null($toSavepoint))
		{
			$driver->transactionCommit();
		}

		/* find how many rows have description='testRollbackSp' :
		 *   - 0 if a savepoint doesn't exist
		 *   - 1 if a savepoint exists
		 */
		$queryCheck = $driver->getQuery(true);
		$queryCheck->select('*')
			->from('#__dbtest_innodb')
			->where("description = 'testRollbackSp'");
		$driver->setQuery($queryCheck);
		$result = $driver->loadRowList();

		$this->assertThat(count($result), $this->equalTo($tupleCount), __LINE__);
	}

	/**
	 * Test isSupported method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testIsSupported()
	{
		$driver = self::getDriver();
		$this->assertThat(\Awf\Database\Driver\Mysqli::isSupported(), $this->isTrue(), __LINE__);
	}

	/**
	 * Test insertObject method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testInsertObject()
	{
		$driver = self::getDriver();
		$sampleData = (object)array(
			'id'          => null,
			'title'       => 'test_insert',
			'start_date'  => '2014-06-17 00:00:00',
			'description' => 'Testing object insert'
		);
		$table = '#__dbtest';
		$db = $driver;

		// Inserting really adds to database
		$db->truncateTable($table);
		$result = $db->insertObject($table, $sampleData);
		$this->assertTrue($result);
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($table)
			->where('title = ' . $db->q($sampleData->title));
		$this->assertNotEmpty($db->setQuery($query)->loadResult());
		$this->assertNull($sampleData->id);

		// Inserting and specifying key updates the object
		$db->truncateTable($table);
		$result = $db->insertObject($table, $sampleData, 'id');
		$this->assertTrue($result);
		$this->assertNotNull($sampleData->id);

		// Bad keys are ignored
		$newSampleData = array_merge((array)$sampleData, array('doesnotexist' => 1234));
		$db->truncateTable($table);
		$result = $db->insertObject($table, $sampleData);
		$this->assertTrue($result);
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($table)
			->where('title = ' . $db->q($sampleData->title));
		$this->assertNotEmpty($db->setQuery($query)->loadResult());

		// "Internal" keys (starting with underscore) and non-scalars are ignored
		$newSampleData = array_merge((array)$sampleData, array('_internal' => 1234, 'baz' => array(1, 2, 3), 'whatever' => (object)array('foo' => 'bar')));
		$db->truncateTable($table);
		$result = $db->insertObject($table, $sampleData);
		$this->assertTrue($result);
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($table)
			->where('title = ' . $db->q($sampleData->title));
		$this->assertNotEmpty($db->setQuery($query)->loadResult());

		// Failures result in exception
		$this->setExpectedException('\RuntimeException');
		$result = $db->insertObject($table . '_foobar', $sampleData, 'id');
	}

	/**
	 * Test updateObject method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testUpdateObject()
	{
		$driver = self::getDriver();
		$sampleData = (object)array(
			'id'          => null,
			'title'       => 'test_insert',
			'start_date'  => '2014-06-17 00:00:00',
			'description' => 'Testing object insert'
		);
		$table = '#__dbtest';
		$db = $driver;

		// Updating full object really updates the database
		$db->truncateTable($table);
		$db->insertObject($table, $sampleData, 'id');
		$newObject = (object)array(
			'id'          => $sampleData->id,
			'title'       => 'test_update',
			'start_date'  => '2005-08-15 18:00:00',
			'description' => 'Updated record'
		);
		$result = $db->updateObject($table, $newObject, 'id');
		$this->assertTrue($result);
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($table)
			->where('title = ' . $db->q($sampleData->title));
		$this->assertEmpty($db->setQuery($query)->loadResult());
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($table)
			->where('title = ' . $db->q($newObject->title));
		$this->assertNotEmpty($db->setQuery($query)->loadResult());

		// Ignoring nulls does not modify data already in the database
		$db->truncateTable($table);
		$db->insertObject($table, $sampleData, 'id');
		$newObject = (object)array(
			'id'          => $sampleData->id,
			'title'       => null,
			'start_date'  => '2005-08-15 18:00:00',
			'description' => 'Updated again record'
		);
		$result = $db->updateObject($table, $newObject, 'id', false);
		$this->assertTrue($result);
		$query = $db->getQuery(true)
			->select('title')
			->from($table)
			->where('id = ' . $db->q($sampleData->id));
		$this->assertEquals($sampleData->title, $db->setQuery($query)->loadResult());

		// "Internal" keys (starting with underscore) and non-scalars are ignored
		$db->truncateTable($table);
		$db->insertObject($table, $sampleData, 'id');
		$newObject = array_merge((array)$sampleData, array(
			'_internal' => 1234,
			'baz' => array(1, 2, 3),
			'whatever' => (object)array('foo' => 'bar'),
		));
		$newObject = (object)$newObject;
		$result = $db->updateObject($table, $newObject, 'id', false);
		$this->assertTrue($result);

		// Wrong ID does not throw error (as no SQL error is raised by the database: it's a valid query with 0 affeced rows)
		$db->truncateTable($table);
		$db->insertObject($table, $sampleData, 'id');
		$newObject = (object)array(
			'id'          => $sampleData->id + 10000,
			'title'       => 'this_will_fail',
			'start_date'  => '2005-08-15 18:00:00',
			'description' => 'Updated again record'
		);
		$result = $db->updateObject($table, $newObject, 'id');
		$this->assertTrue($result);
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($table)
			->where('title = ' . $db->q($newObject->title));
		$this->assertEmpty($db->setQuery($query)->loadResult());

		// Nonexistent fields result in exception
		$db->truncateTable($table);
		$db->insertObject($table, $sampleData, 'id');
		$newObject = array_merge((array)$sampleData, array(
			'iamnothere' => 1234,
		));
		$newObject = (object)$newObject;
		$this->setExpectedException('\RuntimeException');
		$result = $db->updateObject($table, $newObject, 'id', false);
	}

}
