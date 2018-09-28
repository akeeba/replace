<?php
/*
Plugin Name: Akeeba Replace
Plugin URI: https://www.akeebabackup.com
Description: Database mass content replace
Version: 1.0.0
Author: Akeeba Ltd
Author URI: https://www.akeebabackup.com
License: GPLv3
*/

/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

defined('WPINC') or die;

if (defined('AKEEBA_REPLACE_SELF'))
{
	return;
}

define('AKEEBA_REPLACE_SELF', __FILE__);

require_once __DIR__ . '/includes/lib/Autoloader/Autoloader.php';
Akeeba\Replace\Autoloader\Autoloader::getInstance()->addMap('Akeeba\\Replace\\WordPress\\', __DIR__ . '/includes');

\Akeeba\Replace\WordPress\Helper\Application::init();

/****************************************************************************
 * Plugin activation
 ****************************************************************************/

// Plugin activation events
register_activation_hook(__FILE__, array('\Akeeba\Replace\WordPress\Helper\Application', 'onPluginActivate'));
register_deactivation_hook(__FILE__, array('\Akeeba\Replace\WordPress\Helper\Application', 'onPluginDeactivate'));

/****************************************************************************
 * Integrated updates
 ****************************************************************************/
// TODO Port the updater code from our other software
//add_filter ('pre_set_site_transient_update_plugins', array('\Akeeba\Replace\WordPress\Helper\Update', 'getupdates'), 10, 2);
//add_filter ('plugins_api', array('\Akeeba\Replace\WordPress\Helper\Update', 'checkinfo'), 10, 3);
//add_filter ('upgrader_pre_download', array('\Akeeba\Replace\WordPress\Helper\Update', 'addDownloadID'), 10, 3);
//add_filter ('upgrader_package_options', array('\Akeeba\Replace\WordPress\Helper\Update', 'packageOptions'), 10, 2);
//add_action ('upgrader_process_complete', array('\Akeeba\Replace\WordPress\Helper\Update', 'postUpdate'), 10, 2);
//add_filter ('after_plugin_row_akeebareplace/akeebareplace.php', array('\Akeeba\Replace\WordPress\Helper\Update', 'updateMessage'), 10, 3);

/****************************************************************************
 * WordPress administration
 ****************************************************************************/
if (is_admin() && !defined('AKEEBA_REPLACE_DOING_AJAX'))
{
	add_action('admin_menu', array('\Akeeba\Replace\WordPress\Helper\Application', 'onAdminMenu'));
	add_action('network_admin_menu', array('\Akeeba\Replace\WordPress\Helper\Application', 'onAdminMenu'));
	add_filter('set-screen-option', array('\Akeeba\Replace\WordPress\Helper\Application', 'set_option'), 10, 3);
	add_action('plugins_loaded', array('\Akeeba\Replace\WordPress\Helper\Application', 'storeRealRequestAll'), 1);
	add_action('init', array('\Akeeba\Replace\WordPress\Helper\Application', 'startAdminBuffer'), 2);
}

/****************************************************************************
 * WP-CLI integration
 ****************************************************************************/
if (defined('WP_CLI') && WP_CLI)
{
	// TODO WP-CLI integration
}