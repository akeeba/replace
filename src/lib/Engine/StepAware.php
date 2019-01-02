<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine;

use InvalidArgumentException;

/**
 * A trait to implement the StepAwareInterface
 *
 * @package Akeeba\Replace\Engine
 */
trait StepAware
{
	/**
	 * The current engine part step
	 *
	 * @var string
	 */
	protected $step = '';

	/**
	 * The current engine part step
	 *
	 * @var string
	 */
	protected $substep = '';

	/**
	 * Return the current engine part step
	 *
	 * @return  string
	 */
	public function getStep()
	{
		return $this->step;
	}

	/**
	 * Set the current engine part step
	 *
	 * @param   string  $step
	 *
	 * @throws  InvalidArgumentException
	 */
	protected function setStep($step)
	{
		if (!is_string($step))
		{
			throw new InvalidArgumentException(sprintf("Parameter \$step to %s::%s must be a string, %s given", __CLASS__, __METHOD__, gettype($step)));
		}

		$this->step = $step;
	}

	/**
	 * Return the current engine part substep
	 *
	 * @return  string
	 */
	public function getSubstep()
	{
		return $this->substep;
	}

	/**
	 * Set the current engine part substep
	 *
	 * @param   string  $substep
	 *
	 * @throws  InvalidArgumentException
	 */
	protected function setSubstep($substep)
	{
		if (!is_string($substep))
		{
			throw new InvalidArgumentException(sprintf("Parameter \$substep to %s::%s must be a string, %s given", __CLASS__, __METHOD__, gettype($substep)));
		}

		$this->substep = $substep;
	}


}