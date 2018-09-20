<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Core\Helper;

/**
 * Provides information about the usage and limits of PHP memory
 *
 * @package Akeeba\Replace\Engine\Core\Helper
 */
class MemoryInfo
{
	/**
	 * Get the PHP memory limit in bytes
	 *
	 * @return int  Memory limit in bytes
	 */
	public function getMemoryLimit()
	{
		if (!function_exists('ini_get'))
		{
			return null;
		}

		$memLimit = ini_get("memory_limit");

		if ((is_numeric($memLimit) && ($memLimit < 0)) || !is_numeric($memLimit))
		{
			// A negative memory limit means no memory limit, see http://php.net/manual/en/ini.core.php#ini.memory-limit
			return 0;
		}

		$memLimit = $this->humanToIntegerBytes($memLimit);

		return $memLimit;
	}

	/**
	 * Returns the memory currently in use, in bytes.
	 *
	 * The reason we have this trivial method is merely to be able to mock it during testing.
	 *
	 * @return  int
	 *
	 * @codeCoverageIgnore
	 */
	public function getMemoryUsage()
	{
		return memory_get_usage();
	}

	/**
	 * Converts a human formatted size to integer representation of bytes,
	 * e.g. 1M to 1024768
	 *
	 * @param   string  $setting  The value in human readable format, e.g. "1M"
	 *
	 * @return  integer  The value in bytes
	 */
	public function humanToIntegerBytes($setting)
	{
		$val = trim($setting);
		$last = strtolower($val{strlen($val) - 1});

		if (is_numeric($last))
		{
			return $setting;
		}

		switch ($last)
		{
			case 'p':
			case 'pb':
				$val *= 1024;
			case 't':
			case 'tb':
				$val *= 1024;
			case 'g':
			case 'gb':
				$val *= 1024;
			case 'm':
			case 'mb':
				$val *= 1024;
			case 'k':
			case 'kb':
				$val *= 1024;
		}

		return (int) $val;
	}

	/**
	 * Converts an integer to a human formatter representation, e.g. 1024768 to 1M
	 *
	 * @param   int  $size  The size to convert
	 *
	 * @return  string
	 */
	public function integerBytesToHuman($size)
	{
		$unit = array('b', 'KB', 'MB', 'GB', 'TB', 'PB');

		return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
	}
}