<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Engine\Core\Action\Table;

use Akeeba\Replace\Database\Driver\Fake;
use Akeeba\Replace\Database\Metadata\Column;
use Akeeba\Replace\Database\Metadata\Table;
use Akeeba\Replace\Engine\Core\Action\Table\Collation;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Logger\NullLogger;

class CollationTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		require_once AKEEBA_TEST_ROOT . '/Stubs/Database/Driver/Fake.php';
	}

	/**
	 * @param $collation
	 * @param $newCollation
	 * @param $expectedAction
	 * @param $expectedBackup
	 *
	 * @dataProvider providerProcessTable
	 */
	public function testProcessTable($collation, $newCollation, $expectedAction, $expectedBackup)
	{
		$db        = new Fake([
			'prefix' => 'tst_',
		]);
		$logger    = new NullLogger();
		$config    = new Configuration([
			'tableCollation' => $newCollation,
		]);
		$tableMeta = new Table('tst_foobar', 'MyISAM', 123, $collation);
		$columns   = [
			new Column('id', 'bigint(20)', '', 'PRI', true),
			new Column('foo', 'varchar(255)', 'utf8_general_ci', '', true),
		];

		$dummy    = new Collation($db, $logger, $config);
		$response = $dummy->processTable($tableMeta, $columns);

		$actionQueries = $response->getActionQueries();
		$backupQueries = $response->getRestorationQueries();

		if (is_null($expectedAction))
		{
			$this->assertCount(0, $actionQueries);
		}
		else
		{
			$this->assertCount(1, $actionQueries);
			$this->assertEquals($expectedAction, $actionQueries[0]);
		}

		if (is_null($expectedAction))
		{
			$this->assertCount(0, $backupQueries);
		}
		else
		{
			$this->assertCount(1, $backupQueries);
			$this->assertEquals($expectedBackup, $backupQueries[0]);
		}
	}

	public static function providerProcessTable()
	{
		return [
			'No collation change' => [
				// $collation, $newCollation,
				'utf8_general_ci', '',
				// $expectedAction, $expectedBackup
				null, null
			],
			'To UTF8MB4' => [
				// $collation, $newCollation,
				'utf8_general_ci', 'utf8mb4_unicode_ci',
				// $expectedAction
				'ALTER TABLE `tst_foobar` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
				// $expectedBackup
				'ALTER TABLE `tst_foobar` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci',
			],
			'Same collation' => [
				// $collation, $newCollation,
				'utf8_general_ci', 'utf8_general_ci',
				// $expectedAction
				null,
				// $expectedBackup
				null,
			],
		];
	}

}
