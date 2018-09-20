<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Writer;

/**
 * A WriterInterface implementation which does absolutely nothing
 *
 * @package Akeeba\Replace\Writer
 */
class NullWriter implements WriterInterface
{
	public function __construct($filePath, $reset = true)
	{
	}

	public function getFilePath()
	{
		return '';
	}

	public function setMaxFileSize($bytes)
	{
	}

	public function getMaxFileSize()
	{
		return 0;
	}

	public function writeLine($line, $eol = PHP_EOL)
	{
	}

	public function getNumberOfParts()
	{
		return 0;
	}

	public function getListOfParts()
	{
		return [];
	}

	public function reset()
	{
	}

}