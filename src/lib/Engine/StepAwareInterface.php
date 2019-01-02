<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine;

/**
 * Interface to an object that's aware of steps.
 *
 * The part knows that it has to divide its work into small, distinct chunks called steps. Each step may be further
 * divided into smaller bits indicated as substeps. For example, processing a database can be divided into processing
 * each individual table (step) which can further be divided into processing individual rows of each table (substep).
 *
 * @package Akeeba\Replace\Engine
 */
interface StepAwareInterface
{
	/**
	 * Get the name of the engine step this part is processing.
	 *
	 * @return  mixed
	 */
	public function getStep();

	/**
	 * Get the name of the engine substep this part is processing.
	 *
	 * @return  mixed
	 */
	public function getSubstep();
}