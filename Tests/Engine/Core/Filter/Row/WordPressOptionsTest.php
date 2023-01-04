<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

/**
 * Created by PhpStorm.
 * User: sledg
 * Date: 10/10/2018
 * Time: 12:12 PM
 */

namespace Akeeba\Replace\Tests\Engine\Core\Filter\Row;

use Akeeba\Replace\Database\Driver\Fake;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Filter\Row\WordPressOptions;
use Akeeba\Replace\Logger\NullLogger;

class WordPressOptionsTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		require_once AKEEBA_TEST_ROOT . '/Stubs/Database/Driver/Fake.php';
	}

	public static function providerFilter()
	{
		$row = [
			'option_id'    => '123',
			'option_name'  => '',
			'option_value' => 'borg',
			'autoload'     => 'yes',
		];

		return [
			// $tableName, $row, $expected
			'Different table'   => ['tst_borg', $row, true],
			'Different prefix'  => ['borg_options', $row, true],
			'Allowed row'       => ['tst_options', array_merge($row, ['option_name' => 'whatever']), true],
			'Site transient #1' => [
				'tst_options',
				array_merge($row, ['option_name' => '_site_transient_whatever']),
				false,
			],
			'Site transient #2' => ['tst_options', array_merge($row, ['option_name' => '_site_transient_woof']), false],
			'Transient #1'      => ['tst_options', array_merge($row, ['option_name' => '_transient_whatever']), false],
			'Transient #2'      => ['tst_options', array_merge($row, ['option_name' => '_transient_woof']), false],
			'Engine cache'      => [
				'tst_options',
				array_merge($row, ['option_name' => 'akeebareplace_engine_cache']),
				false,
			],
		];
	}

	/**
	 * @param $tableName
	 * @param $row
	 * @param $expected
	 *
	 * @dataProvider providerFilter
	 */
	public function testFilter($tableName, $row, $expected)
	{
		$logger = new NullLogger();
		$db     = new Fake([
			'prefix' => 'tst_',
		]);
		$config = new Configuration([]);
		$dummy  = new WordPressOptions($logger, $db, $config);

		$actual = $dummy->filter($tableName, $row);

		self::assertEquals($expected, $actual);
	}
}
