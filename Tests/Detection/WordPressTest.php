<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Detection;

use Akeeba\Replace\Detection\WordPress;

class WordPressTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @dataProvider \Akeeba\Replace\Tests\Detection\WordPressProvider::testIsRecognisedProvider()
	 */
	public function testIsRecognised($path, $expected)
	{
		$detection = new WordPress($path);

		self::assertEquals($expected, $detection->isRecognised());
	}

	/**
	 * @dataProvider \Akeeba\Replace\Tests\Detection\WordPressProvider::testGetDbInformationProvider()
	 */
	public function testGetDbInformation($path, $configFile, $useTokenizer, $expected)
	{
		$detection = new WordPress($path, $configFile);

		self::assertEquals($expected, $detection->getDbInformation($useTokenizer));
	}
}
