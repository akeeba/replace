<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Engine\Core\Action;

use Akeeba\Replace\Database\Driver\Fake;
use Akeeba\Replace\Engine\Core\Action\ActionAware;
use Akeeba\Replace\Engine\Core\Response\SQL;
use Akeeba\Replace\Engine\ErrorHandling\WarningException;
use Akeeba\Replace\Tests\Stubs\Engine\Core\Action\ActionAwareStub;
use Akeeba\Replace\Tests\vfsAware;
use Akeeba\Replace\Writer\FileWriter;
use Prophecy\Argument;

class ActionAwareTest extends \PHPUnit_Framework_TestCase
{
	use vfsAware;

	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		require_once AKEEBA_TEST_ROOT . '/Stubs/Database/Driver/Fake.php';
	}

	protected function setUp()
	{
		parent::setUp();

		$this->setUp_vfsAware();
	}

	/**
	 * @param SQL $response
	 * @param     $expected
	 *
	 * @dataProvider  providerApplyBackupQueries
	 */
	public function testApplyBackupQueries(SQL $response, $expected)
	{
		$filePath = $this->root->url() . '/test.sql';
		$writer   = new FileWriter($filePath, true);
		$dummy    = new ActionAwareStub();

		$dummy->applyBackupQueries($response, $writer);

		$contents = file_get_contents($filePath);

		self::assertEquals($expected, $contents);
	}

	public static function providerApplyBackupQueries()
	{
		return [
			// SQL $response, $expected
			'No queries' => [
				new SQL([], []), ''
			],
			'One query' => [
				new SQL([], ['Foo']), 'Foo;' . "\n"
			],
			'Two queries' => [
				new SQL([], ['Foo', 'Bar']), "Foo;" . "\n" . "Bar;" . "\n"
			],
		];
	}

	/**
	 * @param SQL            $response
	 * @param     $liveMode
	 * @param     $failOnError
	 * @param     $dbSetToFail
	 * @param     $expectedContent
	 * @param     \Exception $expectedException
	 * @param     $expectedError
	 *
	 * @dataProvider providerApplyActionQueries
	 */
	public function testApplyActionQueries(SQL $response, $liveMode, $failOnError, $dbSetToFail, $expectedContent, $expectedException)
	{
		$filePath = $this->root->url() . '/test.sql';
		$writer   = new FileWriter($filePath, true);
		$dummy    = new ActionAwareStub();
		$db       = new Fake();

		if ($dbSetToFail)
		{
			$prophecy = $this->prophesize(Fake::class);
			$prophecy->setQuery(Argument::type('string'))->will(function () {
				return $this;
			});
			$prophecy->execute()->willThrow(new \RuntimeException('Simulated query execution failure'));
			$db = $prophecy->reveal();
		}

		try
		{
			$dummy->applyActionQueries($response, $writer, $db, $liveMode, $failOnError);
		}
		catch (\Exception $e)
		{
			if (!$failOnError && !is_object($expectedException))
			{
				throw new $e;
			}

			self::assertInstanceOf(get_class($expectedException), $e);
			self::assertEquals($expectedException->getMessage(), $e->getMessage());
		}

		$actualContent = file_get_contents($filePath);
		self::assertEquals($expectedContent, $actualContent);

		if (empty($expectedException))
		{
			self::assertEmpty($dummy->getWarnings());

			return;
		}

		if (!$failOnError)
		{
			$warnings = $dummy->getWarnings();
			self::assertCount(1, $warnings);
			self::assertEquals($expectedException, $warnings[0]);
		}
	}

	public static function providerApplyActionQueries()
	{
		return [
			// SQL $response, $liveMode, $failOnError, $dbSetToFail, $expectedContent, $expectedWarning, $expectedError
			'No query' => [
				// $response
				new SQL([], []),
				// $liveMode, $failOnError, $dbSetToFail
				true, true, true,
				// $expectedContent, $expectedException
				'', null
			],
			// SQL $response, $liveMode, $failOnError, $dbSetToFail, $expectedContent, $expectedWarning, $expectedError
			'Dry run' => [
				// $response
				new SQL(['Foo'], []),
				// $liveMode, $failOnError, $dbSetToFail
				false, true, true,
				// $expectedContent, $expectedException
				'Foo;' . "\n", null
			],
			// SQL $response, $liveMode, $failOnError, $dbSetToFail, $expectedContent, $expectedWarning, $expectedError
			'Failing query, expect error' => [
				// $response
				new SQL(['Foo'], []),
				// $liveMode, $failOnError, $dbSetToFail
				true, true, true,
				// $expectedContent, $expectedException
				'Foo;' . "\n", new \RuntimeException('Database error #0 with message “Simulated query execution failure” when trying to run SQL command Foo')
			],
			// SQL $response, $liveMode, $failOnError, $dbSetToFail, $expectedContent, $expectedWarning, $expectedError
			'Failing query, expect warning' => [
				// $response
				new SQL(['Foo'], []),
				// $liveMode, $failOnError, $dbSetToFail
				true, false, true,
				// $expectedContent, $expectedException
				'Foo;' . "\n", new WarningException('Database error #0 with message “Simulated query execution failure” when trying to run SQL command Foo')
			],
			// SQL $response, $liveMode, $failOnError, $dbSetToFail, $expectedContent, $expectedWarning, $expectedError
			'Working query' => [
				// $response
				new SQL(['Foo'], []),
				// $liveMode, $failOnError, $dbSetToFail
				true, false, false,
				// $expectedContent, $expectedException
				'Foo;' . "\n", null
			],
			// SQL $response, $liveMode, $failOnError, $dbSetToFail, $expectedContent, $expectedWarning, $expectedError
			'Working queries, two of them' => [
				// $response
				new SQL(['Foo', 'Bar'], []),
				// $liveMode, $failOnError, $dbSetToFail
				true, false, false,
				// $expectedContent, $expectedException
				'Foo;' . "\n" . 'Bar;' . "\n", null
			],
		];
	}
}
