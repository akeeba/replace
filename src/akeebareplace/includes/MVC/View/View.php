<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\MVC\View;

abstract class View implements ViewInterface
{
	/**
	 * Return an instance of a Model by name.
	 *
	 * @param   string  $name      The name of the Model to return
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
}
