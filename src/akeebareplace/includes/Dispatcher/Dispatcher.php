<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\Dispatcher;

use Akeeba\Replace\WordPress\Helper\Application;
use Akeeba\Replace\WordPress\MVC\Dispatcher\Dispatcher as AbstractDispatcher;
use Akeeba\Replace\WordPress\MVC\Input\InputInterface;

class Dispatcher extends AbstractDispatcher
{
	/**
	 * Dispatcher constructor.
	 *
	 * @param   InputInterface  $input
	 */
	public function __construct($input = null)
	{
		$this->defaultView = 'Job';

		parent::__construct($input);
	}


	protected function onBeforeRoute($view, $task)
	{
		wp_enqueue_script('akeebareplace-modal', plugins_url('/js/modal.js', AKEEBA_REPLACE_SELF), [], Application::getMediaVersion());
		wp_enqueue_script('akeebareplace-ajax', plugins_url('/js/ajax.js', AKEEBA_REPLACE_SELF), [], Application::getMediaVersion());
		wp_enqueue_script('akeebareplace-system', plugins_url('/js/system.js', AKEEBA_REPLACE_SELF), ['akeebareplace-modal', 'akeebareplace-ajax'], Application::getMediaVersion());
	}
}