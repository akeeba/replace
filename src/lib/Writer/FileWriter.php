<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Writer;

use RuntimeException;

class FileWriter implements WriterInterface
{
	/**
	 * The path to the (nominal) file being written to.
	 *
	 * @var  string
	 */
	protected $filePath = '';

	/**
	 * How many parts are already created?
	 *
	 * @var  int
	 */
	protected $numParts = 0;

	protected $maxFileSize = 0;

	protected $fp = null;

	/**
	 * Create a file writer
	 *
	 * @param   string  $filePath  Absolute file path to the file to write
	 * @param   bool    $reset     Should I delete any existing file(s)?
	 *
	 * @throws  RuntimeException  When we cannot open the file for writing.
	 */
	public function __construct($filePath, $reset = true)
	{
		$this->filePath = $filePath;

		$this->findNumberOfParts();

		if ($reset)
		{
			$this->reset();
		}

		$this->open();
	}

	/**
	 * Maximum allowed file size before we start splitting it into parts. This sets the part size in bytes.
	 *
	 * The default is zero which means that no archive splitting will take place UNLESS we cannot write to
	 * the file. That would indicate that the host applies a maximum file size limit.
	 *
	 * @param   int  $bytes
	 *
	 * @return  void
	 */
	public function setMaxFileSize($bytes)
	{
		$this->maxFileSize = max(0, (int)$bytes);
	}

	/**
	 * Get the maximum file size option.
	 *
	 * @return  int
	 */
	public function getMaxFileSize()
	{
		return $this->maxFileSize;
	}

	/**
	 * Write a line to the file
	 *
	 * @param   string  $line  The line contents
	 * @param   string  $eol   The end-of-line character, defaults to PHP_EOL
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException  When it's impossible to write to a file no matter what we try to do.
	 */
	public function writeLine($line, $eol = PHP_EOL)
	{
		$curPos   = ftell($this->fp);
		$string   = $line . $eol;
		$expected = $this->byteLen($string);

		// Non-zero part size: if we would get past the part size limit create a new part and update $curPos.
		if ($curPos + $expected > $this->maxFileSize)
		{
			$this->close();
			$this->numParts++;
			$this->open();
			$curPos   = ftell($this->fp);
		}

		$written  = fwrite($this->fp, $string);

		// Treat our memory nicely (especially if we have to recurse).
		unset($string);

		// Were we successful?
		if ($expected == $written)
		{
			return;
		}

		// Writing failed after we created a new part file. We have run out of disk space :(
		if ($curPos == 0)
		{
			throw new RuntimeException("It looks like you run out of disk space. I tried writing $expected bytes, onll $written were written. Plase make some more space in your hosting account and retry.");
		}

		/**
		 * Not enough space in this part.
		 *
		 * This means that the host has a limit on the maximum size of files which can be written to by PHP. We tried
		 * writing past that limit and we got a failure. Therefore we can try creating a new part
		 */
		// Truncate this part back to $curPos (undo partial write)
		ftruncate($this->fp, $curPos);
		$this->close();

		// Create a new part and call writeLine again
		$this->numParts++;
		$this->open();
		$this->writeLine($line, $eol);
	}

	/**
	 * How many parts have been created so far?
	 *
	 * @return  int
	 */
	public function getNumberOfParts()
	{
		// Since the first part is part zero, we need to add 1 to the number of current parts
		return $this->numParts + 1;
	}

	/**
	 * Return a list with the absolute file names of the parts created so far.
	 *
	 * @return  string[]
	 */
	public function getListOfParts()
	{
		$ret = [];

		for ($i = 0; $i <= $this->numParts; $i++)
		{
			$ret[] = $this->getPartPath($i);
		}

		return $ret;
	}

	/**
	 * Remove all parts known to us
	 *
	 * @return  void
	 */
	public function reset()
	{
		// In case a file was open
		$this->close();

		// Delete all parts
		$parts = $this->getListOfParts();

		foreach ($parts as $part)
		{
			@unlink($part);
		}

		// Now we have zero parts since we deleted all parts in the filesystem.
		$this->numParts = 0;
	}

	/**
	 * Find out how many parts are already present on the filesystem.
	 *
	 * @return  void
	 */
	protected function findNumberOfParts()
	{
		$this->numParts = 0;

		while (true)
		{
			$partPath = $this->getPartPath($this->numParts);

			if (!file_exists($partPath))
			{
				break;
			}

			$this->numParts++;
		}

		$this->numParts = max(0, $this->numParts - 1);
	}

	/**
	 * Get the filename for a part number. Part numbers start with zero.
	 *
	 * @param   int  $partNumber
	 *
	 * @return  string
	 */
	protected function getPartPath($partNumber)
	{
		if ($partNumber == 0)
		{
			return $this->filePath;
		}

		$dirName   = dirname($this->filePath);
		$baseName  = basename($this->filePath);
		$extension = '';
		$dotPos    = strrpos($baseName, '.');

		if ($dotPos !== false)
		{
			$extension = substr($baseName, $dotPos);
			$baseName  = substr($baseName, 0, $dotPos);
		}

		if (strlen($extension) == 0)
		{
			/**
			 * No extension: files are number foo, foo.01, foo.02, ...
			 */
			$extension = '.';
		}
		else
		{
			/**
			 * With extension: .sql, .s01, .s02, ...
			 */
			$extension = substr($extension, 0, -2);
		}

		$extension .= sprintf('%02u', $partNumber);

		return $dirName . DIRECTORY_SEPARATOR . $baseName . $extension;
	}

	/**
	 * Open the part file for writing
	 *
	 * @return  void
	 */
	protected function open()
	{
		if (is_resource($this->fp))
		{
			$this->close();
		}

		$fileName = $this->getPartPath($this->numParts);
		$this->fp = @fopen($fileName, 'at');

		if ($this->fp === false)
		{
			throw new RuntimeException(sprintf("Cannot open file “%s” for writing.", $fileName));
		}
	}

	/**
	 * Close the currently open part
	 *
	 * @return  void
	 */
	protected function close()
	{
		if (is_resource($this->fp))
		{
			@fclose($this->fp);
		}

		$this->fp = null;
	}

	/**
	 * Reopen the file when the object is unserialized
	 *
	 * @return  void
	 */
	public function __wakeup()
	{
		$this->open();
	}

	/**
	 * Close the file pointer when the object is disposed of.
	 *
	 * @return  void
	 */
	public function __destruct()
	{
		$this->close();
	}

	private function byteLen($string)
	{
		if (function_exists('mb_strlen'))
		{
			return mb_strlen($string, 'ASCII');
		}

		return strlen($string);
	}
}