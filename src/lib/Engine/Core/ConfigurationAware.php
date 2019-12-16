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
 * Trait for classes implementing an Akeeba Replace engine configuration
 *
 * @package Akeeba\Replace\Engine\Core
 */
trait ConfigurationAware
{
	/**
	 * The engine configuration known to the object
	 *
	 * @var  Configuration
	 */
	protected $config;

	/**
	 * Set the configuration
	 *
	 * @param   Configuration  $config
	 *
	 * @return  void
	 */
	protected function setConfig(Configuration $config)
	{
		$this->config = $config;
	}

	/**
	 * Return the configuration object
	 *
	 * @return  Configuration
	 */
	public function getConfig()
	{
		return $this->config;
	}
}