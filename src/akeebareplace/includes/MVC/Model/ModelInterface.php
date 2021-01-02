<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\MVC\Model;

use Akeeba\Replace\WordPress\MVC\Input\InputInterface;

/**
 * An interface to a Model for our MVC
 *
 * @package Akeeba\Replace\WordPress\MVC\Model
 */
interface ModelInterface
{
	/**
	 * Set a state variable. Use a null $value to unset the state variable.
	 *
	 * @param   string  $key    The key to set
	 * @param   mixed   $value  The value to set it to
	 *
	 * @return  void
	 */
	public function setState($key, $value = null);

	/**
	 * Get a state variable
	 *
	 * @param   string  $key      The key to get
	 * @param   mixed   $default  Default value to return if the key does not exist
	 * @param   string  $filter   The filter to apply to an existing value (the default value DOES NOT get filtered)
	 *
	 * @return  mixed
	 */
	public function getState($key, $default, $filter = '');

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
	public function defState($key, $default = null);

	/**
	 * Does the model know of a state variable by this key name?
	 *
	 * @param   string  $key  The key to check if it exists
	 *
	 * @return  bool
	 */
	public function hasState($key);

	/**
	 * Populate the object state from an input object. Only state keys already known to the object are populated.
	 *
	 * @param   InputInterface  $input  The input object to use
	 * @param   bool            $unset  Should I unset state variables not present in the Input?
	 *
	 * @return  void
	 */
	public function setStateFromInput(InputInterface $input, $unset = false);

    /**
     * Returns the name of the Model
     *
     * @return  string
     */
	public function getName();
}