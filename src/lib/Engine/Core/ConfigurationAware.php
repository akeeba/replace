<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
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