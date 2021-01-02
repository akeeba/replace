<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Replacement;

use Akeeba\Replace\Replacement\Replacement;

/**
 * Class ReplacementTest
 * @package Akeeba\Replace\Tests\Replacement
 */
class ReplacementTest extends \PHPUnit_Framework_TestCase
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		//require_once __DIR__ . '/../_data/_makers/classdefs.php';
	}


	/**
	 * @param string $serialized The string to check
	 * @param string $expected   Is it serialized?
	 *
	 * @dataProvider \Akeeba\Replace\Tests\Replacement\ReplacementProvider::testIsSerialisedProvider
	 */
	public function testIsSerialised($serialized, $expected)
	{
		$actual = Replacement::isSerialised($serialized);

		if (!$expected)
		{
			self::assertFalse($actual);

			return;
		}

		self::assertTrue($actual);
	}

	/**
	 * @param string $serialized   The string to check
	 * @param string $from         Replace this thing in the string
	 * @param string $to           Replace with this bit here
	 * @param bool   $identical    True if we are expected to make no changes at all
	 * @param bool   $isSerialized True if the input is serialized data
	 *
	 * @dataProvider \Akeeba\Replace\Tests\Replacement\ReplacementProvider::testReplaceProvider()
	 */
	public function testReplace($serialized, $from, $to, $identical, $isSerialized)
	{
		// Perform the replacement
		$timeStart = microtime(true);
		$new = Replacement::replace($serialized, $from, $to);
		$timeEnd = microtime(true);
		$elapsedTime = $timeEnd - $timeStart;

		self::assertLessThan(10.0, $elapsedTime, "Replacements should not take too long");

		// If no replacements are expected make sure that the two strings are identical
		if ($identical)
		{
			self::assertEquals($serialized, $new, 'No replacements expected but strings differ');

			return;
		}

		// Replacements expected: make sure the two strings are not the same
		self::assertNotEquals($serialized, $new, 'Replacements expected bur strings are identical');

		// Ensure full replacement (the $from string is no longer present in the new string)
		self::assertNotContains($from, $new, 'The search string is still present after replacement');

		// Ensure correct replacement (the $to string is present in the new string)
		self::assertContains($to, $new, 'The replacement string is not present after replacement');

		$result = @unserialize($new);

		if (!$isSerialized)
		{
			self::assertFalse($result, 'We managed to unserialize non-serialized data. WTF?!');

			return;
		}

		// Make sure the serialized data *can* be unserialized correctly
		self::assertNotFalse($result, 'Replaced data cannot be unserialized');

		/**
		 * Note: we do not test for partial object because it takes forever to walk through the unserialized objects in
		 * the complex test cases :(
		 */
	}

	/**
	 * @param $source
	 * @param $from
	 * @param $to
	 * @param $target
	 *
	 * @dataProvider \Akeeba\Replace\Tests\Replacement\ReplacementProvider::testRegExReplaceProvider()
	 */
	public function testRegExReplace($source, $from, $to, $target)
	{
		// Perform the replacement
		$new = Replacement::replace($source, $from, $to, true);

		self::assertEquals($target, $new);
	}
}
