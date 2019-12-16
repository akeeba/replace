<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Replace\Engine;

use InvalidArgumentException;

/**
 * A trait to implement the DomainAwareInterface
 *
 * @package Akeeba\Replace\Engine
 */
trait DomainAware
{
	/**
	 * The current engine domain
	 *
	 * @var string
	 */
	private $domain = '';

	/**
	 * Get the name of the engine domain this part is processing.
	 *
	 * @return  mixed
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * Set the current engine domain
	 *
	 * @param   string  $domain
	 *
	 * @throws InvalidArgumentException
	 */
	protected function setDomain($domain)
	{
		if (!is_string($domain))
		{
			throw new InvalidArgumentException(sprintf("Parameter \$domain to %s::%s must be a string, %s given", __CLASS__, __METHOD__, gettype($domain)));
		}

		$this->domain = $domain;
	}
}