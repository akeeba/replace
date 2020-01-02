<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\MVC\Model;

use Akeeba\Replace\WordPress\MVC\Input\Filter;
use Akeeba\Replace\WordPress\MVC\Input\InputInterface;

/**
 * Abstract model. Really does absolutely nothing by itself.
 *
 * @package Akeeba\Replace\WordPress\MVC\Model
 */
abstract class Model implements ModelInterface
{
	/**
	 * The instance of the input filter object
	 *
	 * @var  Filter
	 */
	protected $filter;

	/**
	 * The model's state
	 *
	 * @var  array
	 */
	protected $state = [];

    /**
     * The name of the Model
     *
     * @var  string
     */
	protected $name = '';

    /**
	 * Return an instance of a Model by name.
	 *
	 * @param   string  $name      The name of the Model to return
	 *
	 * @return  ModelInterface
	 */
	public static function getInstance($name)
	{
		$className = "Akeeba\\Replace\\WordPress\\Model\\" . ucfirst($name);

		if (!class_exists($className))
		{
			throw new \InvalidArgumentException(sprintf("I cannot find model %s (class %s does not exist or cannot be loaded)", $name, $className));
		}

		return new $className();
	}

	/**
	 * Get an instance of an input filter, used for state variable cleanup
	 *
	 * @return  Filter
	 *
	 * @codeCoverageIgnore
	 */
	protected function getFilter()
	{
		if (!is_object($this->filter))
		{
			$this->filter = new Filter();
		}

		return $this->filter;
	}

	/**
	 * Set a state variable. Use a null $value to unset the state variable.
	 *
	 * @param   string  $key    The key to set
	 * @param   mixed   $value  The value to set it to
	 *
	 * @return  void
	 */
	public function setState($key, $value = null)
	{
		if (is_null($value))
		{
			$this->state[$key] = null;

			unset($this->state[$key]);

			return;
		}

		$this->state[$key] = $value;
	}

	/**
	 * Get a state variable
	 *
	 * @param   string  $key      The key to get
	 * @param   mixed   $default  Default value to return if the key does not exist
	 * @param   string  $filter   The filter to apply to an existing value (the default value DOES NOT get filtered)
	 *
	 * @return  mixed
	 */
	public function getState($key, $default, $filter = '')
	{
		if (!array_key_exists($key, $this->state))
		{
			return $default;
		}

		$value = $this->state[$key];

		if ($filter === '')
		{
			return $value;
		}

		return $this->getFilter()->clean($value, $filter);
	}

	/**
	 * Define a state variable, optionally setting its default value. Unlike setState(), a null value is acceptable and
	 * will not result in the state variable being unset.
	 *
	 * Use in the constructor of an object to populate the default state of the object without overwriting any existing
	 * state (e.g. fetched through unserializations).
	 *
	 * @param   string  $key      The state key to define
	 * @param   mixed   $default  The default value for the defined state key
	 *
	 * @return  void
	 */
	public function defState($key, $default = null)
	{
		if (array_key_exists($key, $this->state))
		{
			return;
		}

		$this->state[$key] = $default;
	}

	/**
	 * Does the model know of a state variable by this key name?
	 *
	 * @param   string  $key  The key to check if it exists
	 *
	 * @return  bool
	 *
	 * @codeCoverageIgnore
	 */
	public function hasState($key)
	{
		return array_key_exists($key, $this->state);
	}

	/**
	 * Populate the object state from an input object. Only state keys already known to the object are populated.
	 *
	 * @param   InputInterface  $input  The input object to use
	 * @param   bool            $unset  Should I unset state variables not present in the Input?
	 *
	 * @return  void
	 */
	public function setStateFromInput(InputInterface $input, $unset = false)
	{
		// Get the raw data. Setting the state does not involve filtering (getting the state does)
		$rawData = $input->getData();

		// Overwrite existing state variables with the input values
		foreach ($rawData as $k => $v)
		{
			if (!$this->hasState($k))
			{
				continue;
			}

			$this->state[$k] = $v;
		}

		// If we don't need to unset keys not present in the input data we're done
		if (!$unset)
		{
			return;
		}

		// Unset all state keys which are not present in the input data
		foreach (array_keys($this->state) as $k)
		{
			if (array_key_exists($k, $rawData))
			{
				continue;
			}

			unset($this->state[$k]);
		}
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
     * Returns the name of the Model
     *
     * @return  string
     */
    public function getName()
    {
        if (empty($this->name))
        {
            $this->name = $this->getNameFromClassName();
        }

        return $this->name;
    }
}
