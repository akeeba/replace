<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Database;

use Akeeba\Replace\Database\Driver;

class DriverTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var    Driver
	 */
	protected $instance;

	/**
	 * A store to track if logging is working.
	 *
	 * @var    array
	 */
	protected $logs;

	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		require_once AKEEBA_TEST_ROOT . '/Stubs/Database/Driver/Fake.php';
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp($resetContainer = true)
	{
		$this->instance = new Driver\Fake([
			'database'  => 'mightymouse',
			'prefix'    => 'kot_',
			'nameQuote' => '[]',
		]);
	}

	/**
	 * Tears down the fixture.
	 *
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		// We need this to be empty.
	}

	public function test__call()
	{
		$this->assertEquals($this->instance->q('foo'), $this->instance->quote('foo'), 'q() is an alias of quote()');

		$this->assertEquals($this->instance->qn('foo'), $this->instance->quoteName('foo'), 'qn() is an alias of quoteName()');

		$this->assertNull($this->instance->foobar('foo'), 'Unknown aliases return null');
	}

	public function test__construct()
	{
		$options = [
			'database' => 'mightymouse',
			'prefix'   => 'kot_',
		];

		$dummy = new Driver\Fake($options);

		$actualOptions = $this->getObjectAttribute($dummy, 'options');

		$this->assertArrayHasKey('database', $actualOptions);
		$this->assertEquals('mightymouse', $actualOptions['database']);

		$this->assertArrayHasKey('prefix', $actualOptions);
		$this->assertEquals('kot_', $actualOptions['prefix']);
	}

	public function testGetInstance()
	{
		$optionsOne = [
			'driver'   => 'fake',
			'database' => 'mightymouse',
			'prefix'   => 'kot_',
		];

		$optionsTwo = [
			'driver'   => 'fake',
			'database' => 'dangermouse',
			'prefix'   => 'dng_',
		];

		$driverOne = Driver::getInstance($optionsOne);
		$driverTwo = Driver::getInstance($optionsTwo);

		$this->assertInstanceOf('\\Akeeba\\Replace\\Database\\Driver\\Fake', $driverOne, 'Driver::getInstance() must return correct subclass (test #1)');
		$this->assertInstanceOf('\\Akeeba\\Replace\\Database\\Driver\\Fake', $driverTwo, 'Driver::getInstance() must return correct subclass (test #2)');

		$this->assertNotSame($driverOne, $driverTwo, 'Using different options must return different driver objects');

		$driverThree = Driver::getInstance($optionsTwo);
		$this->assertSame($driverTwo, $driverThree, 'Using the same options must always return the same object');

		$actualOptions = $this->getObjectAttribute($driverOne, 'options');
		$this->assertEquals('mightymouse', $actualOptions['database'], 'Driver options must be passed correctly to each constructor (test #1)');

		$actualOptions = $this->getObjectAttribute($driverTwo, 'options');
		$this->assertEquals('dangermouse', $actualOptions['database'], 'Driver options must be passed correctly to each constructor (test #2)');
	}

	public function testGetConnection()
	{
		$this->assertNull($this->instance->getConnection());
	}

	public function testGetConnectors()
	{
		$connectors = $this->instance->getConnectors();
		$this->assertContains(
			'Mysqli',
			$connectors,
			'The getConnectors method should return an array with Mysqli as an available option.'
		);
	}

	public function testGetCount()
	{
		$this->assertEquals(0, $this->instance->getCount());
	}

	public function testGetDatabase()
	{
		$this->assertEquals('mightymouse', $this->instance->getDatabase());
	}

	public function testGetDateFormat()
	{
		$this->assertThat(
			$this->instance->getDateFormat(),
			$this->equalTo('Y-m-d H:i:s')
		);
	}

	public function testSplitSql()
	{
		$this->assertThat(
			$this->instance->splitSql('SELECT * FROM #__foo;SELECT * FROM #__bar;'),
			$this->equalTo(
				[
					'SELECT * FROM #__foo;',
					'SELECT * FROM #__bar;',
				]
			),
			'splitSql method should split a string of multiple queries into an array.'
		);
	}

	public function testGetPrefix()
	{
		$this->assertThat(
			$this->instance->getPrefix(),
			$this->equalTo('kot_')
		);
	}

	public function testGetNullDate()
	{
		$this->assertThat(
			$this->instance->getNullDate(),
			$this->equalTo('1BC')
		);
	}

	public function testGetMinimum()
	{
		$this->assertThat(
			$this->instance->getMinimum(),
			$this->equalTo('12.1'),
			'getMinimum should return a string with the minimum supported database version number'
		);
	}

	public function testIsMinimumVersion()
	{
		$this->assertThat(
			$this->instance->isMinimumVersion(),
			$this->isTrue(),
			'isMinimumVersion should return a boolean true if the database version is supported by the driver'
		);
	}

	public function testSetDebug()
	{
		$this->assertThat(
			$this->instance->setDebug(true),
			$this->isType('boolean'),
			'setDebug should return a boolean value containing the previous debug state.'
		);
	}

	public function testSetQuery()
	{
		$this->assertThat(
			$this->instance->setQuery('SELECT * FROM #__dbtest'),
			$this->isInstanceOf('Akeeba\Replace\Database\Driver'),
			'setQuery method should return an instance of Akeeba\Replace\Database\Driver.'
		);
	}

	public function testReplacePrefix()
	{
		$this->assertThat(
			$this->instance->replacePrefix('SELECT * FROM #__dbtest'),
			$this->equalTo('SELECT * FROM kot_dbtest'),
			'replacePrefix method should return the query string with the #__ prefix replaced by the actual table prefix.'
		);
	}

	public function testQuote()
	{
		$this->assertThat(
			$this->instance->quote('test', false),
			$this->equalTo("'test'"),
			'Tests the without escaping.'
		);

		$this->assertThat(
			$this->instance->quote('test'),
			$this->equalTo("'_test_'"),
			'Tests the with escaping (default).'
		);

		$this->assertEquals(
			["'_test1_'", "'_test2_'"],
			$this->instance->quote(['test1', 'test2']),
			'Check that the array is quoted.'
		);
	}

	public function testQuoteBoolean()
	{
		$this->assertThat(
			$this->instance->quote(true),
			$this->equalTo("'_1_'"),
			'Tests handling of boolean true with escaping (default).'
		);

		$this->assertThat(
			$this->instance->quote(false),
			$this->equalTo("'__'"),
			'Tests handling of boolean false with escaping (default).'
		);

		$this->assertThat(
			$this->instance->quote(null),
			$this->equalTo("'__'"),
			'Tests handling of null with escaping (default).'
		);

		$this->assertThat(
			$this->instance->quote(42),
			$this->equalTo("'_42_'"),
			'Tests handling of integer with escaping (default).'
		);

		$this->assertThat(
			$this->instance->quote(3.14),
			$this->equalTo("'_3.14_'"),
			'Tests handling of float with escaping (default).'
		);
	}

	public function testQuoteName()
	{
		$this->assertThat(
			$this->instance->quoteName('test'),
			$this->equalTo('[test]'),
			'Tests the left-right quotes on a string.'
		);

		$this->assertThat(
			$this->instance->quoteName('a.test'),
			$this->equalTo('[a].[test]'),
			'Tests the left-right quotes on a dotted string.'
		);

		$this->assertThat(
			$this->instance->quoteName(['a', 'test']),
			$this->equalTo(['[a]', '[test]']),
			'Tests the left-right quotes on an array.'
		);

		$this->assertThat(
			$this->instance->quoteName(['a.b', 'test.quote']),
			$this->equalTo(['[a].[b]', '[test].[quote]']),
			'Tests the left-right quotes on an array.'
		);

		$this->assertThat(
			$this->instance->quoteName(['a.b', 'test.quote'], [null, 'alias']),
			$this->equalTo(['[a].[b]', '[test].[quote] AS [alias]']),
			'Tests the left-right quotes on an array.'
		);

		$this->assertThat(
			$this->instance->quoteName(['a.b', 'test.quote'], ['alias1', 'alias2']),
			$this->equalTo(['[a].[b] AS [alias1]', '[test].[quote] AS [alias2]']),
			'Tests the left-right quotes on an array.'
		);

		$this->assertThat(
			$this->instance->quoteName((object) ['a', 'test']),
			$this->equalTo(['[a]', '[test]']),
			'Tests the left-right quotes on an object.'
		);

		$refl     = new \ReflectionObject($this->instance);
		$property = $refl->getProperty('nameQuote');
		$property->setAccessible(true);
		$property->setValue($this->instance, '/');

		$this->assertThat(
			$this->instance->quoteName('test'),
			$this->equalTo('/test/'),
			'Tests the uni-quotes on a string.'
		);
	}

	public function testTruncateTable()
	{
		$this->assertNull(
			$this->instance->truncateTable('#__dbtest'),
			'truncateTable should not return anything if successful.'
		);
	}

	public function testGetDatabaseNameFromConnection()
	{
		$db = $this->getRealDatabaseConnection();

		$actual   = $db->getDatabaseNameFromConnection();
		$expected = $_ENV['DB_NAME'];
		$this->assertEquals($expected, $actual);
	}

	/**
	 * Create a real database driver connected to our test database. This is required for some tests which definitely
	 * need to run against a real database.
	 *
	 * @return  Driver
	 */
	private function getRealDatabaseConnection()
	{
		// I need a real connection for this test.
		if (Driver\Mysql::isSupported())
		{
			$driverType = 'mysql';
		}
		elseif (Driver\Mysqli::isSupported())
		{
			$driverType = 'mysqli';
		}
		elseif (Driver\Pdomysql::isSupported())
		{
			$driverType = 'pdomysql';
		}
		else
		{
			$this->markTestIncomplete('No supported database driver found');
		}

		$db = Driver::getInstance([
			'driver'   => $driverType,
			'database' => $_ENV['DB_NAME'],
			'host'     => $_ENV['DB_HOST'],
			'user'     => $_ENV['DB_USER'],
			'password' => $_ENV['DB_PASS'],
			'prefix'   => 'akr_',
			'select'   => true,
		]);

		return $db;
	}
}