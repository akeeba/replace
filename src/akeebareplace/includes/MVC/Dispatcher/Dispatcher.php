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
use Akeeba\Replace\WordPress\Helper\WordPress;
use Akeeba\Replace\WordPress\MVC\Controller\Controller;
use Akeeba\Replace\WordPress\MVC\Input\Input;
use Akeeba\Replace\WordPress\MVC\Input\InputInterface;

abstract class Dispatcher implements DispatcherInterface
{
	/**
	 * The Input object we use for routing the request
	 *
	 * @var  InputInterface
	 */
	protected $input;

	/**
	 * The name of the default view to use in the plugin
	 *
	 * @var  string
	 */
	protected $defaultView = 'ControlPanel';

	/**
	 * Dispatcher constructor.
	 *
	 * @param   InputInterface  $input  The Input data object. Null to get a new one from the request & server data.
	 *
	 * @return  void
	 */
	public function __construct($input = null)
	{
		$this->input = $input;
	}

	/**
	 * Routes the application.
	 *
	 * Finds the view and task to use. Loads and instantiates the Controller. Executes the task in the Controller.
	 *
	 * @return  void
	 */
	public function route()
	{
		$input = $this->getInput();

		// Sanity check: is this an administrator page belonging to our plugin?
		if (!$this->isExpectedPageSlug())
		{
			throw new \RuntimeException("Invalid page slug");
		}

		$this->convertLimitStart($input);

		$view = $input->get('view', $this->defaultView);
		$task = $input->get('task', 'display');

		if (method_exists($this, 'onBeforeRoute'))
		{
			$this->onBeforeRoute($view, $task);
		}

		$controller = Controller::getInstance($view, $input);

		$controller->execute($task);

		if (method_exists($this, 'onAfterRoute'))
		{
			$this->onAfterRoute($view, $task);
		}
	}

	/**
	 * Return the Input object. If none exists we create a new one using pristine data, not the one tainted
	 * (addslash'ed) by WordPress.
	 *
	 * @return  InputInterface|Input
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
			$this->input->cookie->setData($this->getPristineData('cookie'));
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
	 *
	 * @codeCoverageIgnore
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

	/**
	 * Is the current page slug something that belongs to our plugin? This prevents any weird invocation of our
	 * Dispatcher from a menu item which does not belong to our plugin.
	 *
	 * @return  bool
	 */
	private function isExpectedPageSlug()
	{
		// The slug is hardcoded in Application::onAdminMenu
		$expectedPage = 'akeebareplace';
		$currentPage  = $this->getInput()->getPath('page', '');

		if ($currentPage == $expectedPage)
		{
			return true;
		}

		if (strpos($currentPage, $expectedPage . '/') !== 0)
		{
			return false;
		}

		return true;
	}

	/**
	 * In Wordpress you can navigate using the links or directly type the page, this function
	 * takes care of converting the "page" value into a "limitstart" one.
	 *
	 * @param   InputInterface   $input
	 *
	 * @codeCoverageIgnore
	 */
	private function convertLimitStart($input)
	{
		/**
		 * WordPress uses the "paged" variable to tell us which page we are using. Like BASIC, it uses indexes starting
		 * at 1. So we get to decrement the page number to show the correct results. Ahem.
		 */
		$paged = $input->get('paged', 0, 'int');
		$paged -= 1;

		$input->remove('paged');

		/**
		 * This is the first page or an invalid value was used. In this case we fall back to 'limitstart' (if it is set)
		 * or display the first page, if limitstart is NOT set.
		 */
		if ($paged <= 0)
		{
			return;
		}

		$limit     = WordPress::get_page_limit();
		$new_start = $paged * $limit;

		$input->set('limitstart', $new_start);
	}

}
