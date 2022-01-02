<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\MVC\Controller;


use Akeeba\Replace\WordPress\MVC\Input\Filter;
use Akeeba\Replace\WordPress\MVC\Input\Input;
use Akeeba\Replace\WordPress\MVC\Input\InputInterface;
use Akeeba\Replace\WordPress\MVC\Model\DataModel;
use Akeeba\Replace\WordPress\MVC\Model\Model;
use Akeeba\Replace\WordPress\MVC\Model\ModelInterface;
use Akeeba\Replace\WordPress\MVC\View\View;
use Akeeba\Replace\WordPress\MVC\View\ViewInterface;

abstract class Controller implements ControllerInterface
{
	/**
	 * The name of this controller
	 *
	 * @var  string
	 */
	protected $name = '';

	/**
	 * The input data object
	 *
	 * @var  InputInterface
	 */
	protected $input;

	/**
	 * The Model associated with the MVC view
	 *
	 * @var  ModelInterface
	 */
	protected $model;

	/**
	 * The View associated with the MVC view
	 *
	 * @var  ViewInterface
	 */
	protected $view;

	/**
	 * The task being executed
	 *
	 * @var  string
	 */
	protected $task = '';

	/**
	 * Controller constructor.
	 *
	 * @param   InputInterface  $input  The input object.
	 * @param   ModelInterface  $model  The Model object for this view
	 * @param   ViewInterface   $view   The View object for this view
	 */
	public function __construct($input = null, $model = null, $view = null)
	{
		// Initialize the name of the view
		if (empty($this->name))
		{
			$this->name = $this->getNameFromClassName();
		}

		// Make sure we have an Input object
		if (!is_object($input) || !($model instanceof InputInterface))
		{
			$input = empty($this->input) ? new Input() : $this->input;
		}

		$this->input = $input;

		// Make sure we have a Model object
		if (!is_object($model) || !($model instanceof ModelInterface))
		{
			$model = empty($this->model) ? Model::getInstance($this->name) : $this->model;
		}

		$this->model = $model;

		// Make sure we have a View object
		if (!is_object($view) || !($view instanceof ViewInterface))
		{
			$view = empty($this->view) ? View::getInstance($this->name, 'html') : $this->view;
		}

		$this->view = $view;
	}

	/**
	 * Return an instance of a Controller by name.
	 *
	 * @param   string          $name   The name of the Controller to return
	 * @param   InputInterface  $input  The input object.
	 * @param   ModelInterface  $model  The Model object for this view
	 * @param   ViewInterface   $view   The View object for this view
	 *
	 * @return  ControllerInterface
	 */
	public static function getInstance($name, $input = null, $model = null, $view = null)
	{
		$className = "Akeeba\\Replace\\WordPress\\Controller\\" . ucfirst($name);

		if (!class_exists($className))
		{
			throw new \InvalidArgumentException(sprintf("I cannot find the Controller %s (class %s does not exist or cannot be loaded)", $name, $className));
		}

		return new $className();
	}



	public function execute($task = 'default')
	{
		if (method_exists($this, 'onBeforeExecute'))
		{
			$this->onBeforeExecute($task);
		}

		$method = 'onBefore' . ucfirst($task);

		if (method_exists($this, $method))
		{
			$this->{$method}();
		}

		if ($this->view->getTask() == '')
		{
			$this->view->setTask($task);
		}

		if (method_exists($this, $task))
		{
			$this->{$task}();
		}
		else
		{
			$this->display('default');
		}

		$method = 'onAfter' . ucfirst($task);

		if (method_exists($this, $method))
		{
			$this->{$method}();
		}

		if (method_exists($this, 'onAfterExecute'))
		{
			$this->onAfterExecute($task);
		}
	}

	protected function display($layout = 'default')
	{
		if (!empty($layout))
		{
			$this->view->setLayout($layout);
		}

		$this->view->setModel($this->model);

		$html = $this->view->display();

		echo $html;
	}

	public function redirect($url)
	{
		if (!wp_redirect($url))
		{
			return;
		}

		exit();
	}

	public function csrfProtection($task = '', $post = false, $source = 'auto')
	{
		if (!in_array($source, ['auto', 'post', 'get']))
		{
			$source = 'auto';
		}

		if ($source == 'auto')
		{
			$source = $post ? 'post' : 'get';
		}

		$token  = $this->input->{$source}->get('_wpnonce', '');
		$action = "get_{$this->name}" . (empty($task) ? '' : "_$task");

		if ($post)
		{
			$action = "post" . substr($action, 3);
		}

		return wp_verify_nonce($token, $action);
	}

	/**
	 * Get the View name from its class name
	 *
	 * @return  string
	 */
	protected function getNameFromClassName()
	{
		// Fetch the name from the full namespace
		$className = get_class($this);
		$parts     = explode('\\', $className);

		return $parts[count($parts) - 1];
	}

	/**
	 * Fetches the item IDs from the request
	 *
	 * @return  array
	 */
	protected function getIDsFromRequest()
	{
		$method = strtolower($this->input->getMethod());

		if ($this->model instanceof DataModel)
		{
			// If we have a DataModel we will look for the PK of the table
			$pk      = $this->model->getPKName();
		}
		else
		{
			// Otherwise we will look for the generic 'id' request key
			$pk = 'id';
		}

		$pkValue = 0;

		if (!empty($pk))
		{
			$pkValue = $this->input->{$method}->getInt($pk, 0);
		}

		if ($pkValue > 0)
		{
			return [$pkValue];
		}

		// No PK found. Let's load the "cb" array from the request.
		$ids    = $this->input->{$method}->get('cb', [], 'array');
		$filter = new Filter();

		$ids = array_map(function ($v) use ($filter) {
			return $filter->clean($v, 'int');
		}, $ids);

		$ids = array_filter($ids, function ($v) {
			return !empty($v);
		});

		return $ids;
	}
}
