<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
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