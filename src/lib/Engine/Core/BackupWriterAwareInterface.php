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