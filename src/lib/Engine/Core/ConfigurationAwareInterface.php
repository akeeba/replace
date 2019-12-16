<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
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