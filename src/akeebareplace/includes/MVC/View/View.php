<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\MVC\View;

use Akeeba\Replace\WordPress\MVC\Input\Filter;
use Akeeba\Replace\WordPress\MVC\Model\Model;
use Akeeba\Replace\WordPress\MVC\Model\ModelInterface;

/**
 * Abstract MVC View
 *
 * @package Akeeba\Replace\WordPress\MVC\View
 */
abstract class View implements ViewInterface
{
	/**
	 * The name of the view. Set up in the constructor or auto-fetched from the namespace.
	 *
	 * @var  string
	 */
	protected $name = '';

	/**
	 * The layout to load. Each task's onBefore<TaskName> can change that.
	 *
	 * @var  string
	 */
	protected $layout = 'default';

	/**
	 * The task being executed.
	 *
	 * This results in onBefore<TaskName> and onAfter<TaskName> methods being executed by render().
	 *
	 * @var  string
	 */
	protected $task = '';

	/**
	 * The models which have been pushed to the View
	 *
	 * @var  ModelInterface[]
	 */
	protected $modelInstances = [];

	/**
	 * Return an instance of a View by name.
	 *
	 * @param   string  $name  The name of the View to return
	 *
	 * @return  ViewInterface
	 */
	public static function getInstance($name)
	{
		$className = "Akeeba\\Replace\\WordPress\\View\\" . ucfirst($name);

		if (!class_exists($className))
		{
			throw new \InvalidArgumentException(sprintf("I cannot find view %s (class %s does not exist or cannot be loaded)", $name, $className));
		}

		return new $className();
	}

	/**
	 * Constructor to the View
	 */
	public function __construct()
	{
		if (empty($this->name))
		{
			$this->name = $this->getNameFromClassName();
		}
	}

	/**
	 * Push a Model object to the View. You can specify an optional name so you can later retrieve it through getModel()
	 *
	 * @param   ModelInterface  $instance  The Model instance to push
	 * @param   string          $name      The name to push it under. Use "default" for the default Model of the View.
	 *
	 * @return  void
	 */
	public function setModel(ModelInterface $instance, $name = 'default')
	{
		$this->modelInstances[$name] = $instance;
	}

	/**
	 * Retrieve a Model from the View.
	 *
	 * @param   string  $name  The name of the model to retrieve.
	 *
	 * @return  ModelInterface|null  The Model or null if no model by that name is found
	 */
	public function getModel($name = 'default')
	{
		$name = strtolower($name);

		if (!isset($this->modelInstances[$name]) && ($name == 'default'))
		{
			$this->setModel(Model::getInstance($this->name));
		}

		if (!isset($this->modelInstances[$name]))
		{
			$this->setModel(Model::getInstance($name), $name);
		}

		if (!isset($this->modelInstances[$name]))
		{
			return null;
		}

		return $this->modelInstances[$name];
	}

	/**
	 * Enqueue a message for display in the next page load
	 *
	 * @param   string  $message  The message to enqueue
	 * @param   string  $type     Message type. Supported values: error, warning, success, info
	 *
	 * @return  void
	 */
	public function enqueueMessage($message, $type = 'error')
	{
		$messages   = get_user_meta(get_current_user_id(), ViewInterface::MESSAGES_META_KEY, true);

		// If the meta key is not defined, we get an empty string instead of an empty array
		if (!$messages)
		{
			$messages = array();
		}

		$messages[] = array('msg' => $messages, 'type' => $type);

		update_user_meta(get_current_user_id(), ViewInterface::MESSAGES_META_KEY, $messages);
	}

	/**
	 * Set the task to be displayed
	 *
	 * @param   string  $task
	 *
	 * @return  void
	 */
	public function setTask($task)
	{
		$this->task = Filter::getInstance()->clean($task, 'cmd');
	}

	/**
	 * Get the active task for the view
	 *
	 * @return  string
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * Set the layout to render.
	 *
	 * @param   string  $layout
	 *
	 * @return  void
	 */
	public function setLayout($layout)
	{
		$this->layout = Filter::getInstance()->clean($layout, 'cmd');
	}

	/**
	 * Get the layout to render
	 *
	 * @return  string
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Get the view template path for the current view
	 *
	 * @param   string  $layout       The layout to render, @see getLayout
	 * @param   string  $subTemplate  The subtemplate to render, appended to $layout with an underscore.
	 *
	 * @return  string
	 */
	public function getViewTemplatePath($layout, $subTemplate = '')
	{
		$pathTemplate = dirname(AKEEBA_REPLACE_SELF) . '/includes/ViewTemplates/%s/%s';
		$viewName     = $this->name;
		$fileName     = $layout . (empty($subTemplate) ? '' : '_' . $subTemplate) . '.php';

		return sprintf($pathTemplate, $viewName, $fileName);
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

		return $parts[count($parts) - 2];
	}

	/**
	 * Escape a string for display
	 *
	 * @param   string  $string  The string to escape.
	 *
	 * @return  string
	 */
	public function escape($string)
	{
		return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Render the page output for the given task.
	 *
	 * @param   string  $subTemplate
	 *
	 * @return  string
	 */
	public function display($subTemplate = '')
	{
		$html = $this->preRender();

		$eventName = 'onBefore' . ucfirst($this->task);

		if (method_exists($this, $eventName))
		{
			$html .= $this->$eventName();
		}

		$html .= $this->getRenderedTemplate($subTemplate);

		$eventName = 'onAfter' . ucfirst($this->task);

		if (method_exists($this, $eventName))
		{
			$html .= $this->$eventName();
		}

		$html .= $this->afterRender();

		return $html;
	}

	/**
	 * Return a WordPress nonce for the specific task of this view.
	 *
	 * @param   string  $task  The task this nonce will be valid for
	 * @param   bool    $post  True for a nonce valid in POST requests only. False for a nonce valid in GET requests only.
	 *
	 * @return  string
	 */
	public function getNonce($task = '', $post = false)
	{
		$action = "get_{$this->name}" . (empty($task) ? '' : "_$task");

		if ($post)
		{
			$action = "post" . substr($action, 3);
		}

		return wp_create_nonce($action);
	}

	/**
	 * Load a template and return its rendered result
	 *
	 * @param   string  $subTemplate
	 *
	 * @return  bool
	 */
	abstract protected function getRenderedTemplate($subTemplate);
}
