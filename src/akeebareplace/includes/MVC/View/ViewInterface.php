<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\MVC\View;

use Akeeba\Replace\WordPress\MVC\Model\ModelInterface;

/**
 * Interface to a View
 *
 * @package Akeeba\Replace\WordPress\MVC\View
 */
interface ViewInterface
{
	const MESSAGES_META_KEY = 'akeebareplace_messages';

	/**
	 * Runs before rendering the body of the application output.
	 *
	 * For example, it's used to rendered any enqueued messages.
	 *
	 * @return  string
	 */
	public function preRender();

	/**
	 * Runs after rendering the body of the application output.
	 *
	 * @return  string
	 */
	public function afterRender();

	/**
	 * Render the page output for the given task.
	 *
	 * @return  string
	 */
	public function display();

	/**
	 * Push a Model object to the View. You can specify an optional name so you can later retrieve it through getModel()
	 *
	 * @param   ModelInterface  $instance  The Model instance to push
	 * @param   string          $name      The name to push it under. Use "default" for the default Model of the View.
	 *
	 * @return  void
	 */
	public function setModel(ModelInterface $instance, $name = 'default');

	/**
	 * Retrieve a Model from the View.
	 *
	 * @param   string  $name  The name of the model to retrieve.
	 *
	 * @return  ModelInterface|null  The Model or null if no model by that name is found
	 */
	public function getModel($name = 'default');

	/**
	 * Escape a string for display
	 *
	 * @param   string  $string  The string to escape.
	 *
	 * @return  string
	 */
	public function escape($string);

	/**
	 * Enqueue a message for display in the next page load
	 *
	 * @param   string  $message  The message to enqueue
	 * @param   string  $type     Message type. Supported values: error, warning, success, info
	 *
	 * @return  void
	 */
	public function enqueueMessage($message, $type = 'error');

	/**
	 * Set the task to be displayed
	 *
	 * @param   string  $task
	 *
	 * @return  void
	 */
	public function setTask($task);

	/**
	 * Get the active task for the view
	 *
	 * @return  string
	 */
	public function getTask();

	/**
	 * Set the layout to render.
	 *
	 * @param   string  $layout
	 *
	 * @return  void
	 */
	public function setLayout($layout);

	/**
	 * Get the layout to render
	 *
	 * @return  string
	 */
	public function getLayout();

	/**
	 * Get the view template path for the current view
	 *
	 * @param   string  $layout       The layout to render, @see getLayout
	 * @param   string  $subTemplate  The subtemplate to render, appended to $layout with an underscore.
	 *
	 * @return  string
	 */
	public function getViewTemplatePath($layout, $subTemplate = '');
}
