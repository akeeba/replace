<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Engine\Core\Filter\Column;

use Akeeba\Replace\Database\Driver\Fake;
use Akeeba\Replace\Database\Metadata\Column;
use Akeeba\Replace\Database\Metadata\Table;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Filter\Column\UserFilters;
use Akeeba\Replace\Logger\NullLogger;

class UserFiltersTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		require_once AKEEBA_TEST_ROOT . '/Stubs/Database/Driver/Fake.php';
	}

	/**
	 * @param array $filters
	 * @param       $expectedCount
	 * @param array $expectedKeys
	 *
	 * @dataProvider providerFilter
	 */
	public function testFilter(array $filters, $expectedCount, array $expectedKeys)
	{
		$logger = new NullLogger();
		$db     = new Fake([
			'prefix' => 'tst_'
		]);
		$config = new Configuration([
			'excludeRows' => $filters
		]);
		$dummy  = new UserFilters($logger, $db, $config);

		$table   = new Table('tst_foobar', 'MyISAM', 123, 'utf8mb4_unicode_520_ci');
		$columns = self::giveMeSomeColumns();

		$actual = $dummy->filter($table, $columns);

		$this->assertCount($expectedCount, $actual);

		if (empty($expectedKeys))
		{
			return;
		}

		$actualKeys = array_keys($actual);

		$this->assertEquals($expectedKeys, $actualKeys);
	}

	public static function providerFilter()
	{
		return [
			// $filters, $expectedCount, $expectedKeys
			'Empty filters' => [
				[], 6, ['test1', 'test2', 'test3', 'test4', 'test5', 'test6']
			],
			'Filters for another table' => [
				[
					'foobar' => ['test1', 'test3', 'test5', 'test6']
				], 6, ['test1', 'test2', 'test3', 'test4', 'test5', 'test6']
			],
			'Filters for table, concrete name' => [
				[
					'tst_foobar' => ['test1', 'test3', 'test5', 'test6']
				], 2, ['test2', 'test4']
			],
			'Filters for table, abstract name' => [
				[
					'#__foobar' => ['test1', 'test3', 'test5', 'test6']
				], 2, ['test2', 'test4']
			],
		];
	}

	protected static function giveMeSomeColumns()
	{
		return [
			'test1' => new Column('test1', 'varchar(255)', 'utf8mb4_unicode_520_ci', '', false),
			'test2' => new Column('test2', 'smalltext', 'utf8mb4_unicode_520_ci', '', false),
			'test3' => new Column('test3', 'longtext', 'utf8mb4_unicode_520_ci', '', false),
			'test4' => new Column('test4', 'mediumtext', 'utf8mb4_unicode_520_ci', '', false),
			'test5' => new Column('test5', 'text', 'utf8mb4_unicode_520_ci', '', false),
			'test6' => new Column('test6', 'int', '', 'PRO', true),
		];
	}

}
