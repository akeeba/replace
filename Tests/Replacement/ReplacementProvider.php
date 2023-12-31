<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Replacement;


class ReplacementProvider
{
	public static function testIsSerialisedProvider()
	{
		$null   = null;
		$int    = 123;
		$float  = 1.23;
		$string = 'This is a test string';

		return [
			'Object'     => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_namespace_complex_utf8.txt'),
				true,
			],
			'Array'      => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_array.txt'),
				true,
			],
			'Null'       => [
				serialize($null),
				true,
			],
			'Integer'    => [
				serialize($int),
				true,
			],
			'Float'      => [
				serialize($float),
				true,
			],
			'String'      => [
				serialize($string),
				true,
			],
			'Not object' => [
				'O:123:Not an object',
				false
			],
			'Not array' => [
				'a:4:Nope',
				false
			],
			'Not string' => [
				's:not:a:string',
				false
			],
			'Malformed' => [
				' ' . file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_array.txt'),
				false
			]
		];
	}

	public function testReplaceProvider()
	{
		return [
			// $serialized, $from, $to, $identical, $isSerialized
			'Replace ASCII in plain text string' => [
				'This is a test and all is well',
				'This is a test',
				'The quick brown fox jumps over the lazy dog',
				false, false
			],
			'Replace UTF8 in plain text string' => [
				'Η αρχαία επιγραφή έγραφε “κάτι”',
				'κάτι',
				'Νίψον ανομήματα μη μόναν όψιν',
				false, false
			],
			'Replace UTF8MB4 in plain text string' => [
				'Emojis are here 🐈👌',
				'🐈',
				'(cat)',
				false, false
			],
			'Replacement string not found in plain text string' => [
				'Emojis are here 🐈👌',
				'Foo',
				'Bar',
				true, false
			],

			// $serialized, $from, $to, $identical, $isSerialized
			'Replacing in string which must not be detected as serialized' => [
				's:yn:tax',
				'yn',
				'FOOBAR',
				false, false
			],
			'Replacing in object which must not be detected as serialized' => [
				'o:12:tax',
				'tax',
				'death',
				false, false
			],

			// $serialized, $from, $to, $identical, $isSerialized
			'Replace ASCII in array' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_array.txt'),
				'This is a test',
				'The quick brown fox jumps over the lazy dog',
				false, true
			],
			'Replace UTF8 in array' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_array.txt'),
				'Αυτό είναι μια δοκιμή',
				'Νίψον ανομήματα μη μόναν όψιν',
				false, true
			],
			'Replace UTF8MB4 in array' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_array.txt'),
				'🐈👌',
				'Cat Hand',
				false, true
			],
			'Replacement string not found in array' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_array.txt'),
				'I do not exist',
				'This should never happen',
				true, true
			],

			'Replace ASCII with ASCII in simple object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_simple_ascii.txt'),
				'http://www.example.com',
				'https://www.akeeba.com',
				false, true
			],
			'Replace ASCII with UTF8 in simple object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_simple_ascii.txt'),
				'http://www.example.com',
				'https://www.παράδειγμα.com',
				false, true
			],
			'Replace UTF8 with ASCII in simple object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_simple_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.akeeba.com',
				false, true
			],
			'Replace UTF8 with UTF8 in simple object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_simple_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.παράδειγμα.com',
				false, true
			],

			// $serialized, $from, $to, $identical, $isSerialized
			'Replace ASCII with ASCII in complex object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_complex_ascii.txt'),
				'http://www.example.com',
				'https://www.akeeba.com',
				false, true
			],
			'Replace ASCII with UTF8 in complex object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_complex_ascii.txt'),
				'http://www.example.com',
				'https://www.παράδειγμα.com',
				false, true
			],
			'Replace UTF8 with ASCII in complex object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_complex_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.akeeba.com',
				false, true
			],
			'Replace UTF8 with UTF8 in complex object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_complex_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.παράδειγμα.com',
				false, true
			],

			// $serialized, $from, $to, $identical, $isSerialized
			'Replace ASCII with ASCII in simple namespaced object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_namespace_simple_ascii.txt'),
				'http://www.example.com',
				'https://www.akeeba.com',
				false, true
			],
			'Replace ASCII with UTF8 in simple namespaced object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_namespace_simple_ascii.txt'),
				'http://www.example.com',
				'https://www.παράδειγμα.com',
				false, true
			],
			'Replace UTF8 with ASCII in simple namespaced object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_namespace_simple_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.akeeba.com',
				false, true
			],
			'Replace UTF8 with UTF8 in simple namespaced object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_namespace_simple_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.παράδειγμα.com',
				false, true
			],

			// $serialized, $from, $to, $identical, $isSerialized
			'Replace ASCII with ASCII in namespaced complex object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_namespace_complex_ascii.txt'),
				'http://www.example.com',
				'https://www.akeeba.com',
				false, true
			],
			'Replace ASCII with UTF8 in namespaced complex object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_namespace_complex_ascii.txt'),
				'http://www.example.com',
				'https://www.παράδειγμα.com',
				false, true
			],
			'Replace UTF8 with ASCII in namespaced complex object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_namespace_complex_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.akeeba.com',
				false, true
			],
			'Replace UTF8 with UTF8 in namespaced complex object' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_namespace_complex_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.παράδειγμα.com',
				false, true
			],

			// $serialized, $from, $to, $identical, $isSerialized
			// This is a **REAL WORLD** example from 27Collective's My Listing template. I have no words.
			'Serialized data in a serialized string, no replacement' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_double.txt'),
				'http://www.nosuchplace.com',
				'https://www.reallynosuchplace.com',
				true, true
			],

			// $serialized, $from, $to, $identical, $isSerialized
			// This is a **REAL WORLD** example.
			'Serialized data in a serialized string, with replacements' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_double_2.txt'),
				'https://nls-check.com',
				'https://akeeba.nls-check.com',
				false, true
			],

			// $serialized, $from, $to, $identical, $isSerialized
			// This is a **REAL WORLD** example from iTheme Security data stored in the wp_options table

			'iTS massive array, no match' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_huge_data.txt'),
				'http://www.nosuchplace.com',
				'https://www.reallynosuchplace.com',
				true, true
			],
			'iTS massive array, same-length match' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_huge_data.txt'),
				'core/lib/includes/function.login-header.php',
				'foo/bar/baz/this/is/a/test/abcdefghijkl.php',
				false, true
			],
			'iTS massive array, different-length match' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/serialized_huge_data.txt'),
				'core/lib/includes/',
				'you/have/been/replaced/',
				false, true
			],

			// $serialized, $from, $to, $identical, $isSerialized
			// This is a **REAL WORLD** example from WP Bakery Page Builder
			'html_encoded site URL' => [
				file_get_contents(AKEEBA_TEST_ROOT . '/_data/html_encoded.txt'),
				'https%3A%2F%2Fmijnvitaliteit.nl',
				'http%3A%2F%2Fwww.example.com',
				false, false
			],
		];
	}

	public static function testRegExReplaceProvider()
	{
		return [
			// $source, $from, $to, $target
			'Simple replacement' => [
				'The quick brown fox jumped over the lazy dog',
				'/quick/',
				'fast',
				'The fast brown fox jumped over the lazy dog',
			],
			'Case-ignored replacement' => [
				'The quick brown fox jumped over the lazy dog',
				'/QUICK/i',
				'fast',
				'The fast brown fox jumped over the lazy dog',
			],
			'Mixed charsets' => [
				'Νίψον ανομήματα μη μόναν όψιν',
				'/ανομήματα/',
				'🐈',
				'Νίψον 🐈 μη μόναν όψιν',
			],
			'Positional arguments' => [
				'42 13 64',
				'/(\d{2,})\s(\d{2,})/',
				'${2} 🐈 ${1} 🐈',
				'13 🐈 42 🐈 64',
			],
		];
	}
}