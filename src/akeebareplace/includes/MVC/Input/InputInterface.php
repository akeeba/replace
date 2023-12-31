<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\MVC\Input;

/**
 * Interface to an input data handling object
 *
 * @package Akeeba\Replace\WordPress\MVC\Input
 */
interface InputInterface
{
	/**
	 * Create an input object from the provided array source. Leave the source null to use $_REQUEST by reference. Pass
	 * a superglobal (like $_GET, $_POST, $_COOKIE, $_REQUEST) to get an input object based on that data by value.
	 *
	 * @param   array  $source   Source data (Optional, default is using $_REQUEST by reference)
	 * @param   array  $options  Options for the Input object
	 *
	 * @return  void
	 */
	public function __construct($source = null, $options = array());

	/**
	 * Get the number of variables.
	 *
	 * @return  integer  The number of variables in the input.
	 *
	 * @see     \Countable::count()
	 */
	public function count();

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     Name of the value to get.
	 * @param   mixed   $default  Default value to return if variable does not exist.
	 * @param   string  $filter   Filter to apply to the value.
	 *
	 * @return  mixed  The filtered input value.
	 */
	public function get($name, $default = null, $filter = 'cmd');

	/**
	 * Gets an array of values from the request.
	 *
	 * @param   array  $vars        Associative array of keys and filter types to apply.
	 * @param   mixed  $datasource  Array to retrieve data from, or null
	 *
	 * @return  mixed  The filtered input data.
	 */
	public function getArray(array $vars, $datasource = null);

	/**
	 * Sets a value
	 *
	 * @param   string  $name   Name of the value to set.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 */
	public function set($name, $value);

	/**
	 * Remove a value from the input
	 *
	 * @param   string  $name  The key name to remove
	 *
	 * @return  void
	 */
	public function remove($name);

	/**
	 * Does a parameter by this name exist in our input data?
	 *
	 * @param   string  $name  The key to check if it exists
	 *
	 * @return  bool  True if it exists
	 */
	public function has($name);

	/**
	 * Define a value. The value will only be set if there's no value for the name or if it is null.
	 *
	 * @param   string  $name   Name of the value to define.
	 * @param   mixed   $value  Value to assign to the input.
	 *
	 * @return  void
	 */
	public function def($name, $value);

	/**
	 * Gets the request method. This is what the server reports, not the request method corresponding to the data we are
	 * manipulating in this object.
	 *
	 * @return  string   The request method.
	 */
	public function getMethod();

	/**
	 * Returns the (raw) input data as a hash array
	 *
	 * @return  array
	 */
	public function getData();

	/**
	 * Replaces the (raw) input data with the given array
	 *
	 * @param   array|object  $data  The raw input data to use
	 *
	 * @return  void
	 */
	public function setData($data);
}
