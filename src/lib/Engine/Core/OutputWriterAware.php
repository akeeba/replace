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
 * Trait for classes implementing an output SQL writer
 *
 * @package Akeeba\Replace\Engine\Core
 */
trait OutputWriterAware
{
	/**
	 * The writer to use for action SQL file output
	 *
	 * @var  WriterInterface
	 */
	protected $outputWriter;

	/**
	 * Get the output writer object
	 *
	 * @return WriterInterface
	 */
	public function getOutputWriter()
	{
		if (empty($this->outputWriter))
		{
			$this->outputWriter = new NullWriter('');
		}

		return $this->outputWriter;
	}

	/**
	 * Set the output writer
	 *
	 * @param   WriterInterface  $outputWriter
	 */
	protected function setOutputWriter(WriterInterface $outputWriter)
	{
		$this->outputWriter = $outputWriter;
	}
}