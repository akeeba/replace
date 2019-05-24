<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\Helper;

/**
 * A helper class to abstract some common functionality we need to perform in WordPress
 *
 * @package Akeeba\Replace\WordPress\Helper
 */
class WordPress
{
	/**
	 * Retrieves the URL to the admin area (single site) or admin area for the network (multisite).
	 *
	 * @param string $path   Optional path relative to the admin URL. Default empty.
	 * @param string $scheme Optional. The scheme to use. Default is 'admin', which obeys force_ssl_admin()
	 *                       and is_ssl(). 'http' or 'https' can be passed to force those schemes.
	 *
	 * @return string Admin URL link with optional path appended.
	 */
	public static function adminUrl($path = '', $scheme = 'admin')
	{
		if (is_multisite())
		{
			return network_admin_url($path, $scheme);
		}

		return admin_url($path, $scheme);
	}

	/**
	 * Fetches the page limit for the current user, retrieving it from WP user options
	 *
	 * @return int
	 */
	public static function get_page_limit()
	{
		// If any WP function is missing, return the default value
		if (
			!function_exists('get_current_user_id') ||
			!function_exists('get_current_screen') ||
			!function_exists('get_user_meta')
		)
		{
			return 20;
		}

		$user          = get_current_user_id();
		$screen        = get_current_screen();
		$screen_option = $screen->get_option('per_page', 'option');
		$limit         = get_user_meta($user, $screen_option, true);

		// If the user never set a value, let's use the default one
		if ($limit === "")
		{
			$limit = 20;
		}

		return $limit;
	}

}
