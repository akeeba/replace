<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Replace\Engine\Core;

use Akeeba\Replace\Writer\WriterInterface;

/**
 * Interface to classes implementing an output SQL writer
 *
 * @package Akeeba\Replace\Engine\Core
 */
interface OutputWriterAwareInterface
{
	/**
	 * Returns the reference to the class' output writer object
	 *
	 * @return  WriterInterface
	 */
	public function getOutputWriter();
}