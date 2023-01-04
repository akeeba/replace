<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Detection;

/**
 * Data provider for the WordPress detection tests
 *
 * Class WordPressProvider
 * @package Akeeba\Replace\Tests\Detection
 */
abstract class WordPressProvider
{
	public static function testIsRecognisedProvider()
	{
		return [
			// $path, $expected
			'Invalid path' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/does-not-exist',
				false
			],
			'Not WordPress' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/negative/nothing',
				false
			],
			'No wp-login.php' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/negative/no-login',
				false
			],
			'No xmlrpc.php' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/negative/no-xmlrpc',
				false
			],
			'No wp-admin' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/negative/no-admin',
				false
			],
			'Regular WordPress' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive',
				true
			],
			'Subfolder installation' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive/subfolder',
				true
			],
		];
	}

	public static function testGetDbInformationProvider()
	{
		$blank = [
			'driver'   => 'mysqli',
			'host'     => '',
			'port'     => '',
			'username' => '',
			'password' => '',
			'name'     => '',
			'prefix'   => '',
			'charset'  => '',
			'collate'  => '',
		];

		$defaultWP = array_merge($blank, [
			'driver'   => 'mysqli',
			'host'     => 'localhost',
			'username' => 'wpuser',
			'password' => 'wppass',
			'name'     => 'wpdb',
			'prefix'   => 'wp_',
			'charset'  => 'utf8mb4',
		]);

		return [
			// ### Negative tests ###
			// $path, $configFile, $useTokenizer, $expected
			'Invalid path' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/does-not-exist',
				'wp-config.php',
				true,
				$blank
			],
			'Invalid path (without tokenizer)' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/does-not-exist',
				'wp-config.php',
				false,
				$blank
			],

			'Not WordPress' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/negative/nothing',
				'wp-config.php',
				true,
				$blank
			],
			'Not WordPress (without tokenizer)' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/negative/nothing',
				'wp-config.php',
				false,
				$blank
			],

			'No wp-login.php' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/negative/no-login',
				'wp-config.php',
				true,
				$blank
			],
			'No wp-login.php (without tokenizer)' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/negative/no-login',
				'wp-config.php',
				false,
				$blank
			],

			'No xmlrpc.php' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/negative/no-xmlrpc',
				'wp-config.php',
				true,
				$blank
			],
			'No xmlrpc.php (without tokenizer)' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/negative/no-xmlrpc',
				'wp-config.php',
				false,
				$blank
			],

			'No wp-admin' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/negative/no-admin',
				'wp-config.php',
				true,
				$blank
			],
			'No wp-admin (without tokenizer)' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/negative/no-admin',
				'wp-config.php',
				false,
				$blank
			],

			// ### Simple configuration ###
			// $path, $configFile, $useTokenizer, $expected
			'Regular WordPress' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive',
				'wp-config.php',
				true,
				$defaultWP
			],
			'Regular WordPress (without tokenizer)' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive',
				'wp-config.php',
				false,
				$defaultWP
			],

			'Subfolder installation' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive/subfolder',
				'wp-config.php',
				true,
				$defaultWP
			],
			'Subfolder installation (without tokenizer)' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive/subfolder',
				'wp-config.php',
				false,
				$defaultWP
			],

			// ### Edge cases in string manipulation (quotes, comments, ...) ###
			// $path, $configFile, $useTokenizer, $expected
			'Escaped single quote' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive',
				'config-escape.php',
				true,
				array_merge($defaultWP, ['username' => 'wp\"user', 'password' => 'wp\'p@ss'])
			],
			'Escaped single quote (without tokenizer)' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive',
				'config-escape.php',
				false,
				array_merge($defaultWP, ['username' => 'wp\"user', 'password' => 'wp\'p@ss'])
			],

			'Escaped double quote and backslash' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive',
				'config-escape-double.php',
				true,
				array_merge($defaultWP, ['password' => "wp\"p\\rss"])
			],
			'Escaped double quote and backslash (without tokenizer)' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive',
				'config-escape-double.php',
				false,
				array_merge($defaultWP, ['password' => "wp\"p\\rss"])
			],

			'Comments, same line with the correct values' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive',
				'config-comments-simple.php',
				true,
				$defaultWP
			],
			'Comments, same line with the correct values (without tokenizer)' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive',
				'config-comments-simple.php',
				false,
				$defaultWP
			],

			'Comments, "old" values following correct ("new") values -- ONLY WITH TOKENIZER' => [
				AKEEBA_TEST_ROOT . '/_data/detection/wordpress/positive',
				'config-comments-confusing.php',
				true,
				$defaultWP
			],
		];
	}
}