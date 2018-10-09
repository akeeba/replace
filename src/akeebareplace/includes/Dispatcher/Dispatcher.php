<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\Dispatcher;

use Akeeba\Replace\WordPress\Helper\Application;
use Akeeba\Replace\WordPress\MVC\Dispatcher\Dispatcher as AbstractDispatcher;

class Dispatcher extends AbstractDispatcher
{
	protected function onBeforeRoute($view, $task)
	{
		wp_enqueue_script('akeebareplace-modal', plugins_url('/js/modal.js', AKEEBA_REPLACE_SELF), [], Application::getMediaVersion());
		wp_enqueue_script('akeebareplace-ajax', plugins_url('/js/ajax.js', AKEEBA_REPLACE_SELF), [], Application::getMediaVersion());
		wp_enqueue_script('akeebareplace-system', plugins_url('/js/system.js', AKEEBA_REPLACE_SELF), ['akeebareplace-modal', 'akeebareplace-ajax'], Application::getMediaVersion());
	}
}