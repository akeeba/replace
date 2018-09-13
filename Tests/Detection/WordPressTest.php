<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
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

		$this->assertEquals($expected, $detection->isRecognised());
	}

	/**
	 * @dataProvider \Akeeba\Replace\Tests\Detection\WordPressProvider::testGetDbInformationProvider()
	 */
	public function testGetDbInformation($path, $configFile, $useTokenizer, $expected)
	{
		$detection = new WordPress($path, $configFile);

		$this->assertEquals($expected, $detection->getDbInformation($useTokenizer));
	}
}
