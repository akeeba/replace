<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\MVC\Controller;


interface ControllerInterface
{
	/**
	 * Execute a task
	 *
	 * @param   string  $task  The task to execute
	 *
	 * @return  mixed
	 */
	public function execute($task = 'default');

	/**
	 * Perform a redirection
	 *
	 * @param   string  $url  The URL to redirect to
	 *
	 * @return  void
	 */
	public function redirect($url);

	/**
	 * Protect against CSRF using WordPress' nonce.
	 *
	 * @param   string  $task  The task the nonce is expected to be valid for
	 * @param   bool    $post  True to check only the POST data. False to check only the GET data.
	 *
	 * @return  bool  True if the nonce check passes.
	 *
	 * @throws  \RuntimeException
	 */
	public function csrfProtection($task = '', $post = false);
}
