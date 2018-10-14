<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Engine\Core;

use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Logger\LoggerInterface;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @param   array  $input
	 * @param   array  $expected
	 *
	 * @return  void
	 *
	 * @dataProvider \Akeeba\Replace\Tests\Engine\Core\ConfigurationProvider::testSetExcludeRowsProvider()
	 */
	public function testSetExcludeRows($input, $expected)
	{
		$dummy = new Configuration([]);

		$refObj    = new \ReflectionObject($dummy);
		$refMethod = $refObj->getMethod('setExcludeRows');
		$refMethod->setAccessible(true);
		$refMethod->invoke($dummy, $input);

		$actual = $dummy->getExcludeRows();

		self::assertEquals($expected, $actual);
	}

	/**
	 * @param   array  $input
	 * @param   array  $expected
	 *
	 * @return  void
	 *
	 * @dataProvider \Akeeba\Replace\Tests\Engine\Core\ConfigurationProvider::testSetExcludeTablesProvider()
	 */
	public function testSetExcludeTables($input, $expected)
	{
		$dummy = new Configuration([]);

		$refObj    = new \ReflectionObject($dummy);
		$refMethod = $refObj->getMethod('setExcludeTables');
		$refMethod->setAccessible(true);
		$refMethod->invoke($dummy, $input);

		$actual = $dummy->getExcludeTables();

		self::assertEquals($expected, $actual);
	}


	public function testSetFromParameters()
	{
		$input = [
			'outputSQLFile'      => '/does/not/matter/output.sql',
			'backupSQLFile'      => '/does/not/matter/backup.sql',
			'logFile'            => '/does/not/matter/foo.log',
			'minLogLevel'        => LoggerInterface::SEVERITY_INFO,
			'liveMode'           => false,
			'allTables'          => false,
			'maxBatchSize'       => 123,
			'excludeTables'      => [
				'#__table_one',
				'#__table_two',
			],
			'excludeRows'        => [
				'#__table1' => ['row1', 'row2',],
				'#__table2' => ['rowA', 'rowB',],
			],
			'regularExpressions' => false,
			'replacements'       => [
				'foo' => 'bar',
				'baz' => 'bat',
			],
			'databaseCollation'  => 'utf8mb4_unicode_520_ci',
			'tableCollation'     => 'utf8mb4_unicode_520_ci',
			'description'        => 'Foo bar baz bat',
		];

		$config = new Configuration($input);

		self::assertEquals($input['outputSQLFile'], $config->getOutputSQLFile());
		self::assertEquals($input['backupSQLFile'], $config->getBackupSQLFile());
		self::assertEquals($input['logFile'], $config->getLogFile());
		self::assertEquals($input['minLogLevel'], $config->getMinLogLevel());
		self::assertEquals($input['liveMode'], $config->isLiveMode());
		self::assertEquals($input['allTables'], $config->isAllTables());
		self::assertEquals($input['maxBatchSize'], $config->getMaxBatchSize());
		self::assertEquals($input['excludeTables'], $config->getExcludeTables());
		self::assertEquals($input['excludeRows'], $config->getExcludeRows());
		self::assertEquals($input['regularExpressions'], $config->isRegularExpressions());
		self::assertEquals($input['replacements'], $config->getReplacements());
		self::assertEquals($input['databaseCollation'], $config->getDatabaseCollation());
		self::assertEquals($input['tableCollation'], $config->getTableCollation());
		self::assertEquals($input['description'], $config->getDescription());

		/** @var Configuration $config */
		$refObj    = new \ReflectionObject($config);
		$refMethod = $refObj->getMethod('setFromParameters');
		$refMethod->setAccessible(true);
		$refMethod->invoke($config, $input);
	}

	public function testToArray()
	{
		$input = [
			'outputSQLFile'      => '/does/not/matter/output.sql',
			'backupSQLFile'      => '/does/not/matter/backup.sql',
			'logFile'            => '/does/not/matter/foo.log',
			'minLogLevel'        => LoggerInterface::SEVERITY_INFO,
			'liveMode'           => false,
			'allTables'          => false,
			'maxBatchSize'       => 123,
			'excludeTables'      => [
				'#__table_one',
				'#__table_two',
			],
			'excludeRows'        => [
				'#__table1' => ['row1', 'row2',],
				'#__table2' => ['rowA', 'rowB',],
			],
			'regularExpressions' => false,
			'replacements'       => [
				'foo' => 'bar',
				'baz' => 'bat',
			],
			'databaseCollation'  => 'utf8mb4_unicode_520_ci',
			'tableCollation'     => 'utf8mb4_unicode_520_ci',
			'description'        => 'Foo bar baz bat',
		];

		$config = new Configuration($input);
		$actual = $config->toArray();

		self::assertEquals($input, $actual);
	}
}
