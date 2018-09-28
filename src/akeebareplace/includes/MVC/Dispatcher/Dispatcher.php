<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\MVC\Dispatcher;

use Akeeba\Replace\WordPress\Helper\Application;
use Akeeba\Replace\WordPress\MVC\Input\Input;
use Akeeba\Replace\WordPress\MVC\Input\InputInterface;

abstract class Dispatcher
{
	/**
	 * The Input object we use for routing the request
	 *
	 * @var  InputInterface
	 */
	protected $input;

	/**
	 * Return the Input object. If none exists we create a new one using pristine data, not the one tainted
	 * (addslash'ed) by WordPress.
	 *
	 * @return  InputInterface
	 *
	 * @codeCoverageIgnore
	 */
	protected function getInput()
	{
		if (empty($this->input))
		{
			// Create a new Input object using pristine data, not the one tainted (addslash'ed) by WordPress.
			$this->input = new Input($this->getPristineData('request'));
			$this->input->get->setData($this->getPristineData('get'));
			$this->input->post->setData($this->getPristineData('post'));
			$this->input->cookies->setData($this->getPristineData('cookies'));
			$this->input->files->setData($this->getPristineData('files'));
			$this->input->env->setData($this->getPristineData('env'));
			$this->input->server->setData($this->getPristineData('server'));
		}

		return $this->input;
	}

	/**
	 * Get the data of a superglobal array without the slashes having been added to it by WordPress.
	 *
	 * @param   string  $key
	 *
	 * @return  array|null
	 */
	protected function getPristineData($key)
	{
		$pristineData = Application::getRealRequest($key);

		if (!is_null($pristineData))
		{
			return $pristineData;
		}

		$globalKey = '_' . strtoupper($key);

		return array_map('stripslashes_deep', $GLOBALS[$globalKey]);
	}
}
