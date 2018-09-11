<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
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
			$this->assertFalse($actual);

			return;
		}

		$this->assertTrue($actual);
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
		$new = Replacement::replace($serialized, $from, $to);

		// If no replacements are expected make sure that the two strings are identical
		if ($identical)
		{
			$this->assertEquals($serialized, $new, 'No replacements expected but strings differ');

			return;
		}

		// Replacements expected: make sure the two strings are not the same
		$this->assertNotEquals($serialized, $new, 'Replacements expected bur strings are identical');

		// Ensure full replacement (the $from string is no longer present in the new string)
		$this->assertNotContains($from, $new, 'The search string is still present after replacement');

		// Ensure correct replacement (the $to string is present in the new string)
		$this->assertContains($to, $new, 'The replacement string is not present after replacement');

		$result = @unserialize($new);

		if (!$isSerialized)
		{
			$this->assertFalse($result, 'We managed to unserialize non-serialized data. WTF?!');

			return;
		}

		// Make sure the serialized data *can* be unserialized correctly
		$this->assertNotFalse($result, 'Replaced data cannot be unserialized');

		/**
		 * Note: we do not test for partial object because it takes forever to walk through the unserialized objects in
		 * the complex test cases :(
		 */
	}
}
