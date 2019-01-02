<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core;

/**
 * Interface to classes implementing an Akeeba Replace engine configuraiton
 *
 * @package Akeeba\Replace\Engine\Core
 */
interface ConfigurationAwareInterface
{
	/**
	 * Return the configuration object
	 *
	 * @return  Configuration
	 */
	public function getConfig();
}