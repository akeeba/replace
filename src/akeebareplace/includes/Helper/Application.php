<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\Helper;

use Akeeba\Replace\WordPress\Dispatcher\Dispatcher;

/**
 * An abstraction to all the code required to set up a custom backend application in WordPress
 *
 * @package  Akeeba\Replace\WordPress\Helper
 */
class Application
{
	/**
	 * Minimum required PHP version for this plugin
	 */
	const MINIMUM_PHP_VERSION = '5.4.0';

	/**
	 * Minimum recommended PHP version
	 */
	const RECOMMENDED_PHP_VERSION = '7.1.0';

	/**
	 * A copy of the $_REQUEST superglobal before WordPress adds slashes all over its content...
	 *
	 * @var  array
	 */
	protected static $realRequest = [];

	/**
	 * The menu page to our plugin, as returned by add_menu_page()
	 *
	 * @var  string
	 */
	protected static $menuPage = '';

	/**
	 * Runs when the plugin is being initialized
	 *
	 * Used with: akeebareplace.php
	 *
	 * @return  void
	 */
	public static function init()
	{
		// Load translations
		load_plugin_textdomain('akeebareplace', false, 'language');

		// Catch out of date PHP versions
		if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, 'lt'))
		{
			include_once dirname(AKEEBA_REPLACE_SELF) . '/includes/ViewTemplates/Common/wrongphp.php';

			return;
		}
	}

	/**
	 * This is used as an entry point to our application.
	 *
	 * Used with: add_menu_page()
	 *
	 * @return  void
	 */
	public static function entryPoint()
	{
		try
		{
			$dispatcher = new Dispatcher();
			$dispatcher->route();
		}
		catch (\Exception $e)
		{
			require_once dirname(AKEEBA_REPLACE_SELF) . '/includes/ViewTemplates/Common/error.php';
		}
	}

	/**
	 * Runs on plugin activation
	 *
	 * Used with: register_activation_hook()
	 *
	 * @return  void
	 */
	public static function onPluginActivate()
	{
		$dbInstaller = new CustomTables();
		$dbInstaller->install();

		// TODO Install default options, see update_option()
	}

	/**
	 * Runs on plugin deactivation (NOT uninstall)
	 *
	 * Used with: register_deactivation_hook()
	 *
	 * @return  void
	 */
	public static function onPluginDeactivate()
	{
		// We actually have nothing to do on mere deactivation of the plugin.
	}

	/**
	 * Set up the administrator menu on single and multi-site installations. Since we require maximum privileges to
	 * display our application there's no point having separate menus.
	 *
	 * Used with: akeebareplace.php
	 *
	 * @return  void
	 */
	public static function onAdminMenu()
	{
		self::$menuPage = add_menu_page(
			'Akeeba Replace',
			'Akeeba Replace',
			'manage_options',
			'akeebareplace',
			[__CLASS__, 'entryPoint'],
			plugins_url('images/logo/replace-24.png', AKEEBA_REPLACE_SELF)
		);

		add_action('load-' . self::$menuPage, [__CLASS__, 'add_options']);
	}

	/**
	 * Store the raw (unquoted) request variables to prevent WordPress from interfering with our code. We are grown-ups,
	 * we can filter out own data, thank you very much.
	 *
	 * @param   string  $key  Which superglobal to store (useful ones: request, get, post, cookie, env, files, server)
	 *
	 * @see  http://stackoverflow.com/questions/8949768/with-magic-quotes-disabled-why-does-php-wordpress-continue-to-auto-escape-my
	 */
	public static function storeRealRequest($key = 'request')
	{
		$key = '_' . strtoupper($key);

		if (!array_key_exists($key, $GLOBALS))
		{
			return;
		}

		self::$realRequest[$key] = $GLOBALS[$key];
	}

	/**
	 * Apply storeRealRequest() to all interesting / useful superglobals.
	 */
	public static function storeRealRequestAll()
	{
		$keys = ['request', 'get', 'post', 'cookie', 'env', 'files', 'server'];

		foreach ($keys as $key)
		{
			self::storeRealRequest($key);
		}
	}

	/**
	 * Return our raw (unquoted) copy of a superglobal. You can NOT modify it, it is returned by value.
	 *
	 * @param   string  $key  Which superglobal to retrieve (useful ones: request, get, post, cookie, env, files, server)
	 *
	 * @return  array|null  The data, or null if there is no such data.
	 */
	public static function getRealRequest($key = 'request')
	{
		$key = '_' . strtoupper($key);

		if (!array_key_exists($key, self::$realRequest))
		{
			return null;
		}

		return self::$realRequest[$key];
	}

	/**
	 * Adds the Screen option to the page
	 */
	public static function add_options()
	{
		$screen = get_current_screen();

		// get out of here if we are not on our settings page
		if (!is_object($screen) || $screen->id != self::$menuPage)
		{
			return;
		}

		$args = [
			'default' => 20,
			'option'  => 'akeebareplace_per_page',
		];

		add_screen_option('per_page', $args);
	}

	/**
	 * Tell WordPress to save our items per page option
	 *
	 * @param   bool    $status  Should this be saved? It's always populated with FALSE.
	 * @param   string  $option  The name of the option WP asks to save.
	 * @param   int     $value   The option value. We need to return it so it gets saved.
	 *
	 * @return  bool|int  False to NOT save the option. Integer to save the option.
	 */
	public static function set_option($status, $option, $value)
	{
		$allowed = array('akeebareplace_per_page');

		if (in_array($option, $allowed))
		{
			return $value;
		}

		return $status;
	}

	/**
	 * Starts output buffering in the admin area. This prevents any admin area output from interfering with redirections
	 * using PHP headers made late into the request, after WordPress has output content.
	 *
	 * @return  void
	 */
	public static function startAdminBuffer()
	{
		$page = plugins_url('', AKEEBA_REPLACE_SELF);

		// Is this an Admin Tools page?
		if (isset($_REQUEST['page']) && ($_REQUEST['page'] === $page) && !defined('AKEEBA_REPLACE_OBFLAG'))
		{
			define('AKEEBA_REPLACE_OBFLAG', 1);
			@ob_start([__CLASS__, 'clearAdminBuffer']);
		}
	}

	/**
	 * Callback function for "startAdminBuffer". Used to actually output the buffer, therefore the admin area contents,
	 * when we are done.
	 *
	 * @param   string  $contents  The content to output
	 *
	 * @return 	string
	 */
	public static function clearAdminBuffer($contents)
	{
		return $contents;
	}

}