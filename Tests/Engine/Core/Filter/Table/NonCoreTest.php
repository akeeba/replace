<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

/**
 * Created by PhpStorm.
 * User: nicholas
 * Date: 20/9/2018
 * Time: 3:51 μμ
 */

namespace Akeeba\Replace\Tests\Engine\Core\Filter\Table;

use Akeeba\Replace\Database\Driver\Fake;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Filter\Table\NonCore;
use Akeeba\Replace\Logger\NullLogger;

class NonCoreTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		require_once AKEEBA_TEST_ROOT . '/Stubs/Database/Driver/Fake.php';
	}

	/**
	 * @param $allTables
	 *
	 * @dataProvider providerBinary
	 */
	public function testFilter($allTables)
	{
		$logger = new NullLogger();
		$db     = new Fake([
			'prefix' => 'test_',
		]);
		$config = new Configuration([
			'allTables' => $allTables,
		]);
		$filter = new NonCore($logger, $db, $config);

		$tables = [
			'test_foo',
			'test_bar',
			'tests_foo',
			'tests_bar',
			'foo',
			'bar',
		];

		$actual = $filter->filter($tables);

		if ($allTables)
		{
			$this->assertEquals($actual, $tables);

			return;
		}

		$this->assertCount(2, $actual);
		$this->assertContains('test_foo', $actual);
		$this->assertContains('test_bar', $actual);
	}

	public static function providerBinary()
	{
		return [
			'With file' => [true],
			'Without file' => [false],
		];
	}

}
