<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\MVC\Controller;

use Akeeba\Replace\WordPress\MVC\Input\Filter;
use Akeeba\Replace\WordPress\MVC\Input\Input;
use Akeeba\Replace\WordPress\MVC\Input\InputInterface;
use Akeeba\Replace\WordPress\MVC\Model\DataModel;
use Akeeba\Replace\WordPress\MVC\Model\DataModelInterface;
use Akeeba\Replace\WordPress\MVC\Model\ModelInterface;
use Akeeba\Replace\WordPress\MVC\View\View;
use Akeeba\Replace\WordPress\MVC\View\ViewInterface;

abstract class DataController extends Controller
{
    /**
     * The name of the Model to use with this Controller. Default: the same as the Controller's name.
     *
     * @var  string
     */
    protected $modelName = '';

    /**
     * The name of the View to use with this Controller. Default: the same as the Controller's name.
     *
     * @var string
     */
    protected $viewName = '';

	/**
	 * The Model associated with the MVC view
	 *
	 * @var  DataModel
	 */
	protected $model;

	/**
	 * Controller constructor.
	 *
	 * @param   InputInterface      $input  The input object.
	 * @param   DataModelInterface  $model  The Model object for this view
	 * @param   ViewInterface       $view   The View object for this view
	 */
    public function __construct(InputInterface $input = null, DataModelInterface $model = null, ViewInterface $view = null)
    {
	    // Initialize the name of the Controller
	    if (empty($this->name))
	    {
		    $this->name = $this->getNameFromClassName();
	    }

	    // Initialize the name of the Model
	    if (empty($this->modelName))
	    {
		    $this->modelName = $this->name;
	    }

	    // Initialize the name of the View
	    if (empty($this->viewName))
	    {
		    $this->viewName = $this->name;
	    }

	    // Make sure we have an Input object
	    if (!is_object($input) || !($model instanceof InputInterface))
	    {
		    $input = empty($this->input) ? new Input() : $this->input;
	    }

	    $this->input = $input;

	    // Make sure we have a Model object
	    if (!is_object($model) || !($model instanceof DataModelInterface) || !($model instanceof ModelInterface))
	    {
		    $model = empty($this->model) ? DataModel::getInstance($this->name) : $this->model;
	    }

	    $this->model     = $model;
	    $this->modelName = $model->getName();

	    // Make sure we have a View object
	    if (!is_object($view) || !($view instanceof ViewInterface))
	    {
		    $view = empty($this->view) ? View::getInstance($this->name, 'html') : $this->view;
	    }

	    $this->view     = $view;
	    $this->viewName = $view->getName();
    }

	/**
	 * Add a new record
	 *
	 * @return  void
	 */
	public function add()
	{
		if (method_exists($this, 'onBeforeAdd'))
		{
			$this->onBeforeAdd();
		}

		/** @var View $view */
		$view = $this->view;
		$view->setLayout('form');
		$view->setTask($this->task);
		$view->item = $this->model->getNewRecord();

		$this->display();
	}

	/**
	 * Edit an existing record
	 *
	 * @return  void
	 */
	public function edit()
	{
		if (method_exists($this, 'onBeforeEdit'))
		{
			$this->onBeforeEdit();
		}

		$ids = $this->getIDsFromRequest();

		/** @var View $view */
		$view = $this->view;

		if (empty($ids) || !is_array($ids) || empty($ids[0]))
		{
			$url = admin_url('admin.php?page=akeebareplace&view=' . $this->name);

			$view->enqueueMessage(__('Could not load record', 'akeebareplace'), 'error');
			$this->redirect($url);

			return;
		}

		// Load the record
		$record = $this->model->getItem($ids[0]);

		if (is_null($record))
		{
			$url = admin_url('admin.php?page=akeebareplace&view=' . $this->name);

			$view->enqueueMessage(__('Could not load record', 'akeebareplace'), 'error');
			$this->redirect($url);
		}

		// Set existing record to the View
		$view->item = $record;
		$view->setLayout('form');
		$view->setTask($this->task);

		$this->display();
	}

	/**
	 * Save a new record or apply edits to a record
	 *
	 * @return  void
	 */
	public function save()
	{
		if (method_exists($this, 'onBeforeSave'))
		{
			$this->onBeforeSave();
		}

		$method = strtolower($this->input->getMethod());
		$isPost = $method == 'post';
		$this->csrfProtection('save', $isPost);

		$msg  = __('The record has been saved', 'akeebareplace');
		$type = 'info';

		/** @var View $view */
		$view = $this->view;

		if (empty($ids) || !is_array($ids) || empty($ids[0]))
		{
			$url = admin_url('admin.php?page=akeebareplace&view=' . $this->name);

			$view->enqueueMessage(__('Could not load record', 'akeebareplace'), 'error');
			$this->redirect($url);

			return;
		}

		// Load the record
		$record = $this->model->getItem($ids[0]);

		if (is_null($record))
		{
			$url = admin_url('admin.php?page=akeebareplace&view=' . $this->name);

			$view->enqueueMessage(__('Could not load record', 'akeebareplace'), 'error');
			$this->redirect($url);
		}

		$newData = $this->input->{$method}->getData();
		$newData = array_merge((array) $record, $newData);

		try
		{
			$this->model->save($newData);

			$success = true;
		}
		catch (\RuntimeException $exception)
		{
			$msg     = $exception->getMessage();
			$type    = 'error';
			$success = false;
		}

		$this->view->enqueueMessage($msg, $type);
		$this->view->setTask('display');

		if (method_exists($this, 'onAfterSave'))
		{
			$this->onAfterSave($success);
		}

		$url = admin_url('admin.php?page=akeebareplace&view=' . $this->name);

		$this->redirect($url);
	}

	/**
	 * Delete one or more records from the database
	 *
	 * @return  void
	 */
	public function delete()
	{
		if (method_exists($this, 'onBeforeDelete'))
		{
			$this->onBeforeDelete();
		}

		$method = strtolower($this->input->getMethod());
		$isPost = $method == 'post';
		$this->csrfProtection('delete', $isPost);

		$msg  = __('The record has been deleted', 'akeebareplace');
		$type = 'info';

		// Get the IDs to delete from the request
		$ids = $this->getIDsFromRequest();

		// Try to delete
		if (!$this->model->delete($ids))
		{
			$msg  = __('The record could not be deleted', 'akeebareplace');
			$type = 'error';
		}

		// Set the message and redirect
		$this->view->enqueueMessage($msg, $type);
		$this->view->setTask('display');

		$url = admin_url('admin.php?page=akeebareplace&view=' . $this->name);

		$this->redirect($url);
	}

	/**
	 * Fetches the item IDs from the request
	 *
	 * @return  array
	 */
	protected function getIDsFromRequest()
	{
		$method = strtolower($this->input->getMethod());

		// First we will look for the PK of the table
		$pk      = $this->model->getPKName();
		$pkValue = 0;

		if (!empty($pk))
		{
			$pkValue = $this->input->{$method}->getInt($pk, 0);
		}

		if ($pkValue > 0)
		{
			return [$pkValue];
		}

		// No PK found. Let's load the "cid" array from the request.
		$ids    = $this->input->{$method}->get('cid', [], 'array');
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