<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\MVC\View;

/**
 * A View class for structured HTML output inside WordPress' backend
 *
 * @package Akeeba\Replace\WordPress\MVC\View
 */
abstract class Html extends Raw
{
	/**
	 * Runs before rendering the body of the application output.
	 *
	 * For example, it's used to rendered any enqueued messages.
	 *
	 * @return  string
	 */
	public function preRender()
	{
		$html = '<div class="wrap">';

		// Retrieve the list of messages stored for the current user
		$messages = get_user_meta(get_current_user_id(), ViewInterface::MESSAGES_META_KEY, true);

		// If the meta key is not defined, we get an empty string instead of an empty array
		if (!is_array($messages) || empty($messages))
		{
			$messages = [];
		}

		update_user_meta(get_current_user_id(), ViewInterface::MESSAGES_META_KEY, []);

		foreach ($messages as $message)
		{
			$class = 'notice notice-' . $message['type'] . ' is-dismissible';
			$html  .= '<div class="' . $class . '"><p>' . $message['msg'] . '</p></div>';
		}

		if ($this->useFEF)
		{
			$html .= '</div>' . "\n" . '<div class="akeeba-renderer-fef akeeba-wp">';
		}

		return $html;
	}

	/**
	 * Runs after rendering the body of the application output.
	 *
	 * @return  string
	 */
	public function afterRender()
	{
		$html = '</div>';

		$this->loadFEFStylesheet();

		return $html;
	}
}
