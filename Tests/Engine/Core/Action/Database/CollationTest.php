<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Engine\Core\Action\Database;

use Akeeba\Replace\Database\Metadata\Database;
use Akeeba\Replace\Database\Driver\Fake;
use Akeeba\Replace\Engine\Core\Action\Database\Collation;
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
	 * @param $charset
	 * @param $collation
	 * @param $newCollation
	 * @param $expectedAction
	 * @param $expectedBackup
	 *
	 * @dataProvider providerProcessDatabase
	 */
	public function testProcessDatabase($charset, $collation, $newCollation, $expectedAction, $expectedBackup)
	{
		$db     = new Fake();
		$logger = new NullLogger();
		$config = new Configuration([
			'databaseCollation' => $newCollation
		]);
		$dbMeta = new Database('foobar', $charset, $collation);

		$dummy    = new Collation($db, $logger, $config);
		$response = $dummy->processDatabase($dbMeta);

		$actionQueries = $response->getActionQueries();
		$backupQueries = $response->getRestorationQueries();

		if (is_null($expectedAction))
		{
			self::assertCount(0, $actionQueries);
		}
		else
		{
			self::assertCount(1, $actionQueries);
			self::assertEquals($expectedAction, $actionQueries[0]);
		}

		if (is_null($expectedAction))
		{
			self::assertCount(0, $backupQueries);
		}
		else
		{
			self::assertCount(1, $backupQueries);
			self::assertEquals($expectedBackup, $backupQueries[0]);
		}
	}

	public static function providerProcessDatabase()
	{
		return [
			'No collation change' => [
				// $charset, $collation, $newCollation,
				'utf8', 'utf8_general_ci', '',
				// $expectedAction, $expectedBackup
				null, null
			],
			'To UTF8MB4' => [
				// $charset, $collation, $newCollation,
				'utf8', 'utf8_general_ci', 'utf8mb4_unicode_ci',
				// $expectedAction
				'ALTER DATABASE `foobar` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
				// $expectedBackup
				'ALTER DATABASE `foobar` CHARACTER SET utf8 COLLATE utf8_general_ci',
			],
			'Same collation' => [
				// $charset, $collation, $newCollation,
				'utf8', 'utf8_general_ci', 'utf8_general_ci',
				// $expectedAction
				null,
				// $expectedBackup
				null,
			],
		];
	}
}
