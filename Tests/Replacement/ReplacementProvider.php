<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
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
				file_get_contents(__DIR__ . '/../_data/serialized_namespace_complex_utf8.txt'),
				true,
			],
			'Array'      => [
				file_get_contents(__DIR__ . '/../_data/serialized_array.txt'),
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
				' ' . file_get_contents(__DIR__ . '/../_data/serialized_array.txt'),
				false
			]
		];
	}

	public function testReplaceProvider()
	{
		// $serialized, $from, $to, $identical, $isSerialized
		return [
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

			'Replace ASCII in array' => [
				file_get_contents(__DIR__ . '/../_data/serialized_array.txt'),
				'This is a test',
				'The quick brown fox jumps over the lazy dog',
				false, true
			],
			'Replace UTF8 in array' => [
				file_get_contents(__DIR__ . '/../_data/serialized_array.txt'),
				'Αυτό είναι μια δοκιμή',
				'Νίψον ανομήματα μη μόναν όψιν',
				false, true
			],
			'Replace UTF8MB4 in array' => [
				file_get_contents(__DIR__ . '/../_data/serialized_array.txt'),
				'🐈👌',
				'Cat Hand',
				false, true
			],
			'Replacement string not found in array' => [
				file_get_contents(__DIR__ . '/../_data/serialized_array.txt'),
				'I do not exist',
				'This should never happen',
				true, true
			],

			'Replace ASCII with ASCII in simple object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_simple_ascii.txt'),
				'http://www.example.com',
				'https://www.akeebabackup.com',
				false, true
			],
			'Replace ASCII with UTF8 in simple object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_simple_ascii.txt'),
				'http://www.example.com',
				'https://www.παράδειγμα.com',
				false, true
			],
			'Replace UTF8 with ASCII in simple object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_simple_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.akeebabackup.com',
				false, true
			],
			'Replace UTF8 with UTF8 in simple object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_simple_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.παράδειγμα.com',
				false, true
			],

			'Replace ASCII with ASCII in complex object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_complex_ascii.txt'),
				'http://www.example.com',
				'https://www.akeebabackup.com',
				false, true
			],
			'Replace ASCII with UTF8 in complex object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_complex_ascii.txt'),
				'http://www.example.com',
				'https://www.παράδειγμα.com',
				false, true
			],
			'Replace UTF8 with ASCII in complex object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_complex_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.akeebabackup.com',
				false, true
			],
			'Replace UTF8 with UTF8 in complex object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_complex_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.παράδειγμα.com',
				false, true
			],


			'Replace ASCII with ASCII in simple namespaced object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_namespace_simple_ascii.txt'),
				'http://www.example.com',
				'https://www.akeebabackup.com',
				false, true
			],
			'Replace ASCII with UTF8 in simple namespaced object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_namespace_simple_ascii.txt'),
				'http://www.example.com',
				'https://www.παράδειγμα.com',
				false, true
			],
			'Replace UTF8 with ASCII in simple namespaced object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_namespace_simple_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.akeebabackup.com',
				false, true
			],
			'Replace UTF8 with UTF8 in simple namespaced object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_namespace_simple_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.παράδειγμα.com',
				false, true
			],

			'Replace ASCII with ASCII in namespaced complex object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_namespace_complex_ascii.txt'),
				'http://www.example.com',
				'https://www.akeebabackup.com',
				false, true
			],
			'Replace ASCII with UTF8 in namespaced complex object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_namespace_complex_ascii.txt'),
				'http://www.example.com',
				'https://www.παράδειγμα.com',
				false, true
			],
			'Replace UTF8 with ASCII in namespaced complex object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_namespace_complex_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.akeebabackup.com',
				false, true
			],
			'Replace UTF8 with UTF8 in namespaced complex object' => [
				file_get_contents(__DIR__ . '/../_data/serialized_namespace_complex_utf8.txt'),
				'http://www.δοκιμή.com',
				'https://www.παράδειγμα.com',
				false, true
			],
		];
	}
}