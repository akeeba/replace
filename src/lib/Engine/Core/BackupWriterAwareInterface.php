<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core;

use Akeeba\Replace\Writer\WriterInterface;

/**
 * Interface to classes implementing a backup SQL writer
 *
 * @package Akeeba\Replace\Engine\Core
 */
interface BackupWriterAwareInterface
{
	/**
	 * Returns the reference to the class' backup writer object
	 *
	 * @return  WriterInterface
	 */
	public function getBackupWriter();
}