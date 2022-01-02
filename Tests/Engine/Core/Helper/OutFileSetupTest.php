<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Engine\Core\Helper;

use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\Helper\OutFileSetup;
use Akeeba\Replace\Logger\FileLogger;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Logger\NullLogger;
use Akeeba\Replace\Tests\vfsAware;
use Akeeba\Replace\Writer\FileWriter;
use Akeeba\Replace\Writer\NullWriter;
use Akeeba\Replace\Writer\WriterInterface;

class OutFileSetupTest extends \PHPUnit_Framework_TestCase
{
	use vfsAware;

	protected function setUp()
	{
		parent::setUp();

		$this->setUp_vfsAware();
	}

	/**
	 * @param $dateTime
	 * @param $timeZone
	 * @param $expectedDate
	 * @param $expectedTimezone
	 *
	 * @dataProvider \Akeeba\Replace\Tests\Engine\Core\Helper\OutFileSetupProvider::provider__construct()
	 */
	public function test__construct($dateTime, $timeZone, $expectedDate, $expectedTimezone)
	{
		$dummy = new OutFileSetup($dateTime, $timeZone);

		self::assertEquals($expectedDate, $this->getObjectAttribute($dummy, 'dateTime'));
		self::assertEquals($expectedTimezone, $this->getObjectAttribute($dummy, 'timeZone'));
	}

	/**
	 * @param $dateTime
	 * @param $timeZone
	 * @param $dateTimeParam
	 * @param $expected
	 *
	 * @dataProvider \Akeeba\Replace\Tests\Engine\Core\Helper\OutFileSetupProvider::providerGetLocalTimeStamp()
	 */
	public function testGetLocalTimeStamp($dateTime, $timeZone, $dateTimeParam, $expected)
	{
		$dummy  = new OutFileSetup($dateTime, $timeZone);
		$format = 'Y-m-d H:i:s T';
		$actual = $dummy->getLocalTimeStamp($format, $dateTimeParam);

		self::assertEquals($expected, $actual);
	}

	public function testGetVariables()
	{
		$dummy    = new OutFileSetup('2018-01-02 03:04:05', 'Asia/Nicosia');
		$actual   = $dummy->getVariables();
		$expected = [
			'[DATE]'       => '20180102',
			'[YEAR]'       => '2018',
			'[MONTH]'      => '01',
			'[DAY]'        => '02',
			'[TIME]'       => '030405',
			'[TIME_TZ]'    => '030405eet',
			'[WEEK]'       => '01',
			'[WEEKDAY]'    => 'Tuesday',
			'[GMT_OFFSET]' => '+0200',
			'[TZ]'         => 'eet',
			'[TZ_RAW]'     => 'EET',
		];
		self::assertEquals($expected, $actual);
	}

	/**
	 * @param $expectFile
	 *
	 * @dataProvider providerBinary
	 */
	public function testMakeOutputWriter($expectFile)
	{
		$filePath   = $this->root->url() . '/[DATE]-[TIME_TZ]-[FOO].sql';
		$expectName = '20180102-030405eet-bar.sql';

		$dummy  = new OutFileSetup('2018-01-02 03:04:05', 'Asia/Nicosia');
		$config = new Configuration([
			'outputSQLFile' => $expectFile ? $filePath : '',
		]);
		$actual = $dummy->makeOutputWriter($config, true, ['[FOO]' => 'bar']);

		if (!$expectFile)
		{
			self::assertInstanceOf(WriterInterface::class, $actual);
			self::assertInstanceOf(NullWriter::class, $actual);
			self::assertEquals('', $actual->getFilePath());
			self::assertFalse($this->root->hasChild($expectName));

			return;
		}

		self::assertInstanceOf(WriterInterface::class, $actual);
		self::assertInstanceOf(FileWriter::class, $actual);
		self::assertEquals($this->root->url() . '/' . $expectName, $actual->getFilePath());
		self::assertTrue($this->root->hasChild($expectName));
	}

	/**
	 * @param $expectFile
	 *
	 * @dataProvider providerBinary
	 */
	public function testMakeBackupWriter($expectFile)
	{
		$filePath   = $this->root->url() . '/[DATE]-[TIME_TZ]-[FOO].sql';
		$expectName = '20180102-030405eet-bar.sql';

		$dummy  = new OutFileSetup('2018-01-02 03:04:05', 'Asia/Nicosia');
		$config = new Configuration([
			'backupSQLFile' => $expectFile ? $filePath : '',
		]);
		$actual = $dummy->makeBackupWriter($config, true, ['[FOO]' => 'bar']);

		if (!$expectFile)
		{
			self::assertInstanceOf(WriterInterface::class, $actual);
			self::assertInstanceOf(NullWriter::class, $actual);
			self::assertEquals('', $actual->getFilePath());
			self::assertFalse($this->root->hasChild($expectName));

			return;
		}

		self::assertInstanceOf(WriterInterface::class, $actual);
		self::assertInstanceOf(FileWriter::class, $actual);
		self::assertEquals($this->root->url() . '/' . $expectName, $actual->getFilePath());
		self::assertTrue($this->root->hasChild($expectName));
	}

	/**
	 * @param $expectFile
	 *
	 * @dataProvider providerBinary
	 */
	public function testMakeLogger($expectFile)
	{
		$filePath   = $this->root->url() . '/[DATE]-[TIME_TZ]-[FOO].log';
		$expectName = '20180102-030405eet-bar.log';

		$dummy  = new OutFileSetup('2018-01-02 03:04:05', 'Asia/Nicosia');
		$config = new Configuration([
			'logFile' => $expectFile ? $filePath : '',
		]);
		$actual = $dummy->makeLogger($config, true, ['[FOO]' => 'bar']);

		if (!$expectFile)
		{
			self::assertInstanceOf(LoggerInterface::class, $actual);
			self::assertInstanceOf(NullLogger::class, $actual);
			self::assertFalse($this->root->hasChild($expectName));

			return;
		}

		self::assertInstanceOf(LoggerInterface::class, $actual);
		self::assertInstanceOf(FileLogger::class, $actual);
		self::assertTrue($this->root->hasChild($expectName));
	}

	public static function providerBinary()
	{
		return [
			'With file' => [true],
			'Without file' => [false],
		];
	}
}
