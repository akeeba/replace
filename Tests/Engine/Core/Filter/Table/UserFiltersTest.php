<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Engine\Core\Filter\Table;

use Akeeba\Replace\Database\Driver\Fake;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Filter\Table\UserFilters;
use Akeeba\Replace\Logger\NullLogger;

class UserFiltersTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		require_once AKEEBA_TEST_ROOT . '/Stubs/Database/Driver/Fake.php';
	}

	public function testFilter()
	{
		$logger = new NullLogger();
		$db     = new Fake([
			'prefix' => 'test_',
		]);
		$config = new Configuration([
			'excludeTables' => [
				'#__foo',
				'tests_bar',
			]
		]);
		$filter = new UserFilters($logger, $db, $config);

		$tables = [
			'test_foo',
			'test_bar',
			'tests_foo',
			'tests_bar',
			'foo',
			'bar',
		];

		$actual = $filter->filter($tables);

		self::assertCount(4, $actual);
		self::assertNotContains('test_foo', $actual);
		self::assertNotContains('tests_bar', $actual);

	}
}
