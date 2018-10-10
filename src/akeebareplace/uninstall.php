<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * This file is called when you are uninstalling Akeeba Replace. Its job is to:
 *
 * -- remove all Akeeba Replace options stored in your database
 * -- remove all database tables created by Akeeba Restore
 *
 * Kindly remember that removal of Akeeba Replace's files and folders is the sole responsibility of WordPress itself.
 * Moreover, please do remember that if you manually remove Akeeba Replace's files this uninstallation script may not
 * run correctly or at all.
 */

/**
 * Make sure we are being called from WordPress itself
 */
defined('WPINC') or die;

/**
 * Make sure that the plugin is *REALLY* being uninstalled and that's why we are here.
 */
defined('WP_UNINSTALL_PLUGIN') or die;

if (!defined('AKEEBA_REPLACE_SELF'))
{
	define('AKEEBA_REPLACE_SELF', __FILE__);
}

require_once __DIR__ . '/includes/lib/Autoloader/Autoloader.php';
Akeeba\Replace\Autoloader\Autoloader::getInstance()->addMap('Akeeba\\Replace\\WordPress\\', __DIR__ . '/includes');

// Load translations
load_plugin_textdomain('akeebareplace', false, 'language');

/**
 * Remove database tables
 */
$dbInstaller = new \Akeeba\Replace\WordPress\Helper\CustomTables();
$dbInstaller->uninstall();

/**
 * Remove WordPress options
 */
delete_option('akeebareplace_engine_cache');
delete_option('akeebareplace_min_execution');
delete_option('akeebareplace_max_execution');
delete_option('akeebareplace_runtime_bias');