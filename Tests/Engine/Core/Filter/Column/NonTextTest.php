<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Engine\Core\Filter\Column;

use Akeeba\Replace\Database\Driver\Fake;
use Akeeba\Replace\Database\Metadata\Column;
use Akeeba\Replace\Database\Metadata\Table;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Filter\Column\NonText;
use Akeeba\Replace\Logger\NullLogger;

class NonTextTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		require_once AKEEBA_TEST_ROOT . '/Stubs/Database/Driver/Fake.php';
	}

	public function testFilter()
	{
		$logger = new NullLogger();
		$db     = new Fake();
		$config = new Configuration([]);
		$dummy  = new NonText($logger, $db, $config);

		$table   = new Table('foobar', 'MyISAM', 123, 'utf8mb4_unicode_520_ci');
		$columns = self::giveMeSomeColumns();

		$actual = $dummy->filter($table, $columns);

		self::assertCount(5, $actual);
		self::assertArrayNotHasKey('test6', $actual);
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
