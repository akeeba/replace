<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
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