<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Replacement;

/**
 * A class to intelligently handle replacement of plain text and serialized data.
 */
class Replacement
{
	/**
	 * Replace data in a plain text or a serialized string. We automatically detect if the string looks like serialized
	 * data.
	 *
	 * @param   string  $original  The data to replace into
	 * @param   string  $from      The string to search for
	 * @param   string  $to        The string to replace with
	 * @param   bool    $regEx     Treat $from as Regular Expression
	 *
	 * @return  string
	 */
	public static function replace($original, $from, $to, $regEx = false)
	{
		if (self::isSerialised($original))
		{
			return self::replaceSerialized($original, $from, $to, $regEx);
		}

		return self::replacePlainText($original, $from, $to, $regEx);
	}

	/**
	 * Does this string look like PHP serialised data? Please note that this is a quick pre-test. It's not 100% correct
	 * but it should work in all significant real-world cases.
	 *
	 * @param   string  $string  The string to test
	 *
	 * @return  boolean  True if it looks like serialised data
	 */
	public static function isSerialised($string)
	{
		$scalar     = ['s:', 'i:', 'b:', 'd:', 'r:'];
		$structured = ['a:', 'O:', 'C:'];

		// Is it null?
		if ($string == 'N;')
		{
			return true;
		}

		// Is it scalar?
		if (in_array(substr($string, 0, 2), $scalar))
		{
			return substr($string, -1) == ';';
		}

		// Is it structured?
		if (!in_array(substr($string, 0, 2), $structured))
		{
			return false;
		}

		// Do we have a semicolon to denote the object length?
		$semicolonPos = strpos($string, ':', 3);

		if ($semicolonPos === false)
		{
			return false;
		}

		// Do we have another semicolon afterwards?
		$secondPos = strpos($string, ':', $semicolonPos + 1);

		if ($secondPos === false)
		{
			return false;
		}

		// Is the length an integer?
		$length = substr($string, $semicolonPos + 1, $secondPos - $semicolonPos - 1);

		return (int) $length == $length;
	}

	/**
	 * Replace data in a plain text string. Used internally.
	 *
	 * @param   string  $original  The data to replace into
	 * @param   string  $from      The string to search for
	 * @param   string  $to        The string to replace with
	 * @param   bool    $regEx     Treat $from as Regular Expression
	 *
	 * @return  string
	 */
	protected static function replacePlainText($original, $from, $to, $regEx = false)
	{
		if (!$regEx)
		{
			return str_replace($from, $to, $original);
		}

		return preg_replace($from, $to, $original);
	}

	/**
	 * Replace data in a serialized string. Used internally.
	 *
	 * The simplest and fastest approach. We use regular expressions to split the serialized data at the serialized
	 * string boundaries, then replace the strings and adjust the length.
	 *
	 * @param   string  $serialized  The serialized data to replace into
	 * @param   string  $from        The string to search for
	 * @param   string  $to          The string to replace with
	 * @param   bool    $regEx       Treat $from as Regular Expression
	 *
	 * @return  string
	 */
	protected static function replaceSerialized($serialized, $from, $to, $regEx = false)
	{
		/**
		 * This pattern matches a serialised string. It returns its length and everything to the right of the leading
		 * double quote (serialised string, its closing double quote and semicolon and any data in the original string.
		 */
		$pattern = '/s:(\d{1,}):\"/iU';
		$ret     = '';

		while (true)
		{
			// If there is no more serialised data we're done.
			if (empty($serialized))
			{
				break;
			}

			// Extract the useful information from the serialised string
			$patternMatch = preg_split($pattern, $serialized, 2, PREG_SPLIT_DELIM_CAPTURE);

			// Position 0: content before the pattern. If it's non empty add a verbatim chunk.
			if (!empty($patternMatch[0]))
			{
				$ret .= $patternMatch[0];
			}

			// If the verbatim element was the only element found (no pattern matches) we are done.
			if (count($patternMatch) === 1)
			{
				break;
			}

			// Position 1 captures the serialised string length
			$contentLength = $patternMatch[1];
			// Extract the serialized string data and run a recursive replacement on it.
			$content = self::replace(substr($patternMatch[2], 0, $contentLength), $from, $to, $regEx);
			// Calculate the new serialized data length
			$newLength = function_exists('mb_strlen')
				? mb_strlen($content, 'ASCII')
				: strlen($content);
			// Reformat and append the new serialised string to the output string.
			$ret       .= sprintf('s:%d:"%s"', $newLength, $content) . ';';

			// Treat memory kindly
			unset($content);

			/**
			 * Skip the trailing double quote and semicolon of the original serialised string data. The rest of the
			 * string needs to go through this loop again.
			 */
			$serialized = substr($patternMatch[2], $contentLength + 2);
		}

		return $ret;
	}

}