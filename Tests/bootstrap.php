<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

// Maximise error reporting.
ini_set('zend.ze1_compatibility_mode', '0');
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);

// This is necessary for the session testing
ini_set('session.use_only_cookies', false);
ini_set('session.use_cookies', false);
ini_set('session.use_trans_sid', false);
ini_set('session.cache_limiter', null);

// Timezone fix; avoids errors printed out by PHP 5.3.3+
if (function_exists('date_default_timezone_get') && function_exists('date_default_timezone_set'))
{
	if (function_exists('error_reporting'))
	{
		$oldLevel = error_reporting(0);
	}

	$serverTimezone = @date_default_timezone_get();

	if (empty($serverTimezone) || !is_string($serverTimezone))
	{
		$serverTimezone = 'UTC';
	}

	if (function_exists('error_reporting'))
	{
		error_reporting($oldLevel);
	}

	@date_default_timezone_set($serverTimezone);
}

// Include the library autoloader
if (false == include __DIR__ . '/../src/lib/Autoloader/Autoloader.php')
{
	echo 'ERROR: The Autoloader was not found' . PHP_EOL;

	exit(1);
}

// Register the test classes with our PSR-4 autoloader
\Akeeba\Replace\Autoloader\Autoloader::getInstance()->addMap('Akeeba\\Replace\\Tests\\', __DIR__);

// Load the environment variables from the .env file(s)
$dotEnv = new \Dotenv\Dotenv(__DIR__);
$dotEnv->load();

// Make sure the tests are properly configured
$dotEnv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
$dotEnv->required(['DB_HOST', 'DB_NAME', 'DB_USER'])->notEmpty();