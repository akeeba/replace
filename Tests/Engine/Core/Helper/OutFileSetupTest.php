<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
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

		$this->assertEquals($expectedDate, $this->getObjectAttribute($dummy, 'dateTime'));
		$this->assertEquals($expectedTimezone, $this->getObjectAttribute($dummy, 'timeZone'));
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

		$this->assertEquals($expected, $actual);
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
		$this->assertEquals($expected, $actual);
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
			$this->assertInstanceOf(WriterInterface::class, $actual);
			$this->assertInstanceOf(NullWriter::class, $actual);
			$this->assertEquals('', $actual->getFilePath());
			$this->assertFalse($this->root->hasChild($expectName));

			return;
		}

		$this->assertInstanceOf(WriterInterface::class, $actual);
		$this->assertInstanceOf(FileWriter::class, $actual);
		$this->assertEquals($this->root->url() . '/' . $expectName, $actual->getFilePath());
		$this->assertTrue($this->root->hasChild($expectName));
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
			$this->assertInstanceOf(WriterInterface::class, $actual);
			$this->assertInstanceOf(NullWriter::class, $actual);
			$this->assertEquals('', $actual->getFilePath());
			$this->assertFalse($this->root->hasChild($expectName));

			return;
		}

		$this->assertInstanceOf(WriterInterface::class, $actual);
		$this->assertInstanceOf(FileWriter::class, $actual);
		$this->assertEquals($this->root->url() . '/' . $expectName, $actual->getFilePath());
		$this->assertTrue($this->root->hasChild($expectName));
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
			$this->assertInstanceOf(LoggerInterface::class, $actual);
			$this->assertInstanceOf(NullLogger::class, $actual);
			$this->assertFalse($this->root->hasChild($expectName));

			return;
		}

		$this->assertInstanceOf(LoggerInterface::class, $actual);
		$this->assertInstanceOf(FileLogger::class, $actual);
		$this->assertTrue($this->root->hasChild($expectName));
	}

	public static function providerBinary()
	{
		return [
			'With file' => [true],
			'Without file' => [false],
		];
	}
}
