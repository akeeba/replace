<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\MVC\Input;


use BadMethodCallException;
use Countable;
use Serializable;

/**
 * An Input data handling object
 *
 * @method  integer  getInt($name, $default)
 * @method  integer  getInteger($name, $default)
 * @method  integer  getUint($name, $default)
 * @method  float    getFloat($name, $default)
 * @method  float    getDouble($name, $default)
 * @method  boolean  getBool($name, $default)
 * @method  boolean  getBoolean($name, $default)
 * @method  string   getWord($name, $default)
 * @method  string   getAlnum($name, $default)
 * @method  string   getCmd($name, $default)
 * @method  string   getBase64($name, $default)
 * @method  string   getString($name, $default)
 * @method  string   getHtml($name, $default)
 * @method  string   getPath($name, $default)
 * @method  string   getUsername($name, $default)
 *
 * @package Akeeba\Replace\WordPress\MVC\Input
 */
class Input implements InputInterface, Serializable, Countable
{
	/**
	 * Filter object to use.
	 *
	 * @var  Filter
	 */
	protected $filter = null;

	/**
	 * Input data
	 *
	 * @var  array
	 */
	protected $data = array();

	/**
	 * Input objects
	 *
	 * @var  array
	 */
	protected $inputs = array();

	/**
	 * Input options
	 *
	 * @var  array
	 */
	protected $options = array();

	/**
	 * Flag to detect if I already populated all the inputs
	 *
	 * @var bool
	 */
	private static $inputsLoaded = false;

	/**
	 * Create an input object from the provided array source. Leave the source null to use $_REQUEST by reference. Pass
	 * a superglobal (like $_GET, $_POST, $_COOKIE, $_REQUEST) to get an input object based on that data by value.
	 *
	 * @param   array  $source   Source data (Optional, default is using $_REQUEST by reference)
	 * @param   array  $options  Options for the Input object
	 *
	 * @return  void
	 */
	public function __construct($source = null, $options = array())
	{
		$this->options = $options;

		if (!isset($options['filter']))
		{
			$options['filter'] = Filter::getInstance();
		}

		$this->filter = $options['filter'];

		$this->data = $source;

		// @codeCoverageIgnoreStart
		if (is_null($source))
		{
			$this->data = &$_REQUEST;
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Magic method to get an input object based on the superglobal (e.g. get, post, cookie, env, ...)
	 *
	 * @param   mixed  $name  Name of the input object to retrieve.
	 *
	 * @return  self  The request input object
	 */
	public function __get($name)
	{
		if (!isset($this->inputs[$name]))
		{
			$className   = __NAMESPACE__ . '\\' . ucfirst($name);
			$superGlobal = '_' . strtoupper($name);

			if (class_exists($className))
			{
				$this->inputs[$name] = new $className(null);
			}

			if (!isset($this->inputs[$name]) && isset($GLOBALS[$superGlobal]))
			{
				$this->inputs[$name] = new self($GLOBALS[$superGlobal]);
			}
		}

		if (!isset($this->inputs[$name]))
		{
			throw new \InvalidArgumentException(sprintf("Unknown input type “%s”", $name));
		}

		return $this->inputs[$name];
	}

	/**
	 * Get the number of variables.
	 *
	 * @return  integer  The number of variables in the input.
	 *
	 * @see     \Countable::count()
	 *
	 * @codeCoverageIgnore
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 * @param   string  $filter   Filter to apply to the value.
	 *
	 * @return  mixed  The filtered input value.
	 */
	public function get($name, $default = null, $filter = 'cmd')
	{
		if (isset($this->data[$name]))
		{
			return $this->filter->clean($this->data[$name], $filter);
		}

		return $default;
	}

	/**
	 * Gets an array of values from the request.
	 *
	 * @param   array  $vars        Associative array of keys and filter types to apply.
	 * @param   mixed  $datasource  Array to retrieve data from, or null
	 *
	 * @return  mixed  The filtered input data.
	 */
	public function getArray(array $vars, $datasource = null)
	{
		$results = array();

		foreach ($vars as $k => $v)
		{
			if (is_array($v))
			{
				if (is_null($datasource))
				{
					$results[$k] = $this->getArray($v, $this->get($k, null, 'array'));

					continue;
				}

				$results[$k] = $this->getArray($v, $datasource[$k]);

				continue;
			}

			if (is_null($datasource))
			{
				$results[$k] = $this->get($k, null, $v);

				continue;
			}

			if (isset($datasource[$k]))
			{
				$results[$k] = $this->filter->clean($datasource[$k], $v);

				continue;
			}

			$results[$k] = $this->filter->clean(null, $v);
		}

		return $results;
	}

	/**
	 * Sets a value
	 *
	 * @param   string  $name   Name of the value to set.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	public function set($name, $value)
	{
		$this->data[$name] = $value;
	}

	/**
	 * Does a parameter by this name exist in our input data?
	 *
	 * @param   string  $name  The key to check if it exists
	 *
	 * @return  bool  True if it exists
	 */
	public function has($name)
	{
		return array_key_exists($name, $this->data);
	}

	/**
	 * Remove a value from the input
	 *
	 * @param   string  $name  The key name to remove
	 *
	 * @return  void
	 */
	public function remove($name)
	{
		if (!$this->has($name))
		{
			return;
		}

		unset($this->data[$name]);
	}

	/**
	 * Define a value. The value will only be set if there's no value for the name or if it is null.
	 *
	 * @param   string  $name   Name of the value to define.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 */
	public function def($name, $value)
	{
		if (isset($this->data[$name]))
		{
			return;
		}

		$this->data[$name] = $value;
	}

	/**
	 * Magic method to get filtered input data.
	 *
	 * @param   string  $name       Name of the filter type prefixed with 'get'.
	 * @param   array   $arguments  [0] The name of the variable [1] The default value.
	 *
	 * @return  mixed   The filtered input value.
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) == 'get')
		{
			$filter = substr($name, 3);

			$default = null;

			if (isset($arguments[1]))
			{
				$default = $arguments[1];
			}

			return $this->get($arguments[0], $default, $filter);
		}

		// @codeCoverageIgnoreStart
		throw new BadMethodCallException(sprintf("Input objects do not know how to handle the “%s” method", $name));
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Gets the request method. This is what the server reports, not the request method corresponding to the data we are
	 * manipulating in this object.
	 *
	 * @return  string   The request method.
	 *
	 * @codeCoverageIgnore
	 */
	public function getMethod()
	{
		$method = strtoupper($_SERVER['REQUEST_METHOD']);

		return $method;
	}

	/**
	 * Method to serialize the input.
	 *
	 * @return  string  The serialized input.
	 */
	public function serialize()
	{
		// Load all of the inputs.
		$this->loadAllInputs();

		// Remove $_ENV and $_SERVER from the inputs.
		$inputs = $this->inputs;
		unset($inputs['env']);
		unset($inputs['server']);

		// Serialize the data and inputs.
		return serialize(array($this->options, $this->data, $inputs));
	}

	/**
	 * Method to unserialize the input.
	 *
	 * @param   string  $input  The serialized input.
	 *
	 * @return  void
	 */
	public function unserialize($input)
	{
		// Unserialize the data, and inputs.
		list($this->options, $this->data, $this->inputs) = unserialize($input);

		// Load the filter.
		$this->filter = Filter::getInstance();

		if (isset($this->options['filter']))
		{
			$this->filter = $this->options['filter'];
		}
	}

	/**
	 * Method to load all of the global inputs.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	protected function loadAllInputs()
	{
		if (!self::$inputsLoaded)
		{
			// Load up all the globals.
			foreach ($GLOBALS as $global => $data)
			{
				// Check if the global starts with an underscore.
				if (strpos($global, '_') === 0)
				{
					// Convert global name to input name.
					$global = strtolower($global);
					$global = substr($global, 1);

					// Get the input.
					$this->$global;
				}
			}

			self::$inputsLoaded = true;
		}
	}

	/**
	 * Returns the (raw) input data as a hash array
	 *
	 * @return  array
	 *
	 * @codeCoverageIgnore
	 */
	public function getData()
	{
		return (array)$this->data;
	}

	/**
	 * Replaces the (raw) input data with the given array
	 *
	 * @param   array|object  $data  The raw input data to use
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	public function setData($data)
	{
		$this->data = (array)$data;
	}

}
