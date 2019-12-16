<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Replace\Engine\Core;


use Akeeba\Replace\Writer\NullWriter;
use Akeeba\Replace\Writer\WriterInterface;

/**
 * Trait for classes implementing a backup SQL writer
 *
 * @package Akeeba\Replace\Engine\Core
 */
trait BackupWriterAware
{
	/**
	 * The writer to use for backup SQL file output
	 *
	 * @var  WriterInterface
	 */
	protected $backupWriter;

	/**
	 * Get the backup writer object
	 *
	 * @return WriterInterface
	 */
	public function getBackupWriter()
	{
		if (empty($this->backupWriter))
		{
			$this->backupWriter = new NullWriter('');
		}

		return $this->backupWriter;
	}

	/**
	 * Set the backup writer
	 *
	 * @param   WriterInterface $backupWriter
	 */
	protected function setBackupWriter(WriterInterface $backupWriter)
	{
		$this->backupWriter = $backupWriter;
	}
}