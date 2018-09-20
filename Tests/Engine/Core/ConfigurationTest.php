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
use Prophecy\Prophet;

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
		$dummy->setExcludeRows($input);
		$actual = $dummy->getExcludeRows();

		$this->assertEquals($expected, $actual);
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
		$dummy->setExcludeTables($input);
		$actual = $dummy->getExcludeTables();

		$this->assertEquals($expected, $actual);
	}


	public function testSetPerTableClasses()
	{
		$dummy = new Configuration([]);
		$input = [
			self::class,
			'GuaranteedToNotExist',
		];
		$expected = [
			self::class
		];
		$dummy->setPerTableClasses($input);
		$actual = $dummy->getPerTableClasses();

		$this->assertEquals($expected, $actual);
	}

	public function testSetPerDatabaseClasses()
	{
		$dummy = new Configuration([]);
		$input = [
			self::class,
			'GuaranteedToNotExist',
		];
		$expected = [
			self::class
		];
		$dummy->setPerDatabaseClasses($input);
		$actual = $dummy->getPerDatabaseClasses();

		$this->assertEquals($expected, $actual);

	}

	public function testSetPerRowClasses()
	{
		$dummy = new Configuration([]);
		$input = [
			self::class,
			'GuaranteedToNotExist',
		];
		$expected = [
			self::class
		];
		$dummy->setPerRowClasses($input);
		$actual = $dummy->getPerRowClasses();

		$this->assertEquals($expected, $actual);

	}

	public function testSetFromParameters()
	{
		$input = [
			'outputSQLFile'      => '/does/not/matter/output.sql',
			'backupSQLFile'      => '/does/not/matter/backup.sql',
			'minLogLevel'        => LoggerInterface::SEVERITY_INFO,
			'liveMode'           => false,
			'perDatabaseClasses' => [
				self:: class,
			],
			'perTableClasses'    => [
				self:: class,
			],
			'perRowClasses'      => [
				self:: class,
			],
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
		];

		$config = $this->getMockBuilder(Configuration::class)
			->setMethodsExcept([
				'setFromParameters'
			])
			->setConstructorArgs([$input])
			->getMock();

		$config->expects($this->once())->method('setOutputSQLFile');
		$config->expects($this->once())->method('setBackupSQLFile');
		$config->expects($this->once())->method('setMinLogLevel');
		$config->expects($this->once())->method('setLiveMode');
		$config->expects($this->once())->method('setPerDatabaseClasses');
		$config->expects($this->once())->method('setPerTableClasses');
		$config->expects($this->once())->method('setPerRowClasses');
		$config->expects($this->once())->method('setAllTables');
		$config->expects($this->once())->method('setMaxBatchSize');
		$config->expects($this->once())->method('setExcludeTables');
		$config->expects($this->once())->method('setExcludeRows');
		$config->expects($this->once())->method('setRegularExpressions');
		$config->expects($this->once())->method('setReplacements');
		$config->expects($this->once())->method('setDatabaseCollation');
		$config->expects($this->once())->method('setTableCollation');

		/** @var Configuration $config */
		$config->setFromParameters($input);
	}

	public function testToArray()
	{
		$input = [
			'outputSQLFile'      => '/does/not/matter/output.sql',
			'backupSQLFile'      => '/does/not/matter/backup.sql',
			'minLogLevel'        => LoggerInterface::SEVERITY_INFO,
			'liveMode'           => false,
			'perDatabaseClasses' => [
				self:: class,
			],
			'perTableClasses'    => [
				self:: class,
			],
			'perRowClasses'      => [
				self:: class,
			],
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
		];

		$config = new Configuration($input);
		$actual = $config->toArray();

		$this->assertEquals($input, $actual);
	}
}
