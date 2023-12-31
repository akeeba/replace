<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Replacement;

/**
 * A class to intelligently handle replacement of plain text and serialized data.
 */
class Replacement
{
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
		/**
		 * Scalar values.
		 *
		 * s: String
		 * i: Integer
		 * b: Boolean
		 * d: Decimal (float)
		 * r: Backreference (https://wiki.php.net/rfc/custom_object_serialization). Not a scalar but always stores an
		 *    int, so for our purposes it can be treated as a scalar.
		 */
		$scalar     = ['s:', 'i:', 'b:', 'd:', 'r:'];
		/**
		 * Structured values are the different representation of arrays and objects.
		 *
		 * a: Array
		 * O: Object which is NOT implementing the Serializable interface
		 * C: Object implementing Serializable stored in C format (https://wiki.php.net/rfc/custom_object_serialization)
		 */
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
		$semicolonPos = strpos($string, ':', 1);

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
		$length    = substr($string, $semicolonPos + 1, $secondPos - $semicolonPos - 1);
		$intLength = intval($length);

		if ((string) ($intLength) !== $length)
		{
			return false;
		}

		// You cannot have negative data lengths!
		if ($intLength < 0)
		{
			return false;
		}

		/**
		 * Just checking if the length is an integer is not enough. See the test cases 'Not object' and 'Not array'
		 * where we have something that looks like serialised data BUT the very next character renders it invalid.
		 *
		 * This is what we check here. The very next character after the length and colon.
		 *
		 * Notes on why even empty objects and arrays still have a character afterwards:
		 *
		 * - Null objects still have a non-zero length after the `O:` because this is followed by the class name. For
		 *   example, a serialised empty stdClass looks like this:  O:8:"stdClass":0:{}
		 * - Empty arrays look like this: a:0:{}  Therefore they have a character after the second colon.
		 */
		$oType            = substr($string, $semicolonPos - 1, 1);
		$afterSecondColon = (strlen($string) > $secondPos)
			? substr($string, $secondPos + 1, 1)
			: null;

		switch ($oType)
		{
			// Object, Class: after the second colon I need double quotes (classname)
			// String: after the second colon I need double quotes (literal string)
			case 'O':
			case 'C':
			case 's':
				return $afterSecondColon === '"';

			// Array: after the second colon I need an opening curly brace
			case 'a':
				return $afterSecondColon === '{';

			// Integer, float: numeric or negative sign
			case 'i':
			case 'd':
				return in_array($afterSecondColon, ['0','1','2','3','4','5','6','7','8','9','-'], true);

			// Anything else CAN NOT have two colons!
			default:
				return false;
		}

		/** @noinspection PhpUnreachableStatementInspection This is to prevent future bugs if we refactor this method */
		return false;
	}

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
		// Only run a replacement if the data seems to match our criteria (only works for PLAIN TEXT searches)
		if (!$regEx && (strpos($original, $from) === false))
		{
			return $original;
		}

		/**
		 * Special case: $from and $to are same-length.
		 *
		 * Even if I have serialized data I can use the MUCH faster plain text replacement. I only need to use the
		 * computationally expensive serialised data replacement if the replacement is of a different length.
		 */
		if (!$regEx && (strlen($from) == strlen($to)))
		{
			return self::replacePlainText($original, $from, $to, $regEx);
		}

		// Serialised data
		if (self::isSerialised($original))
		{
			// Columns over AKEEBA_REPLACE_MAXIMUM_COLUMN_SIZE (default: 1MB) use the faster, precarious replacement
			$maxColumnSize = defined('AKEEBA_REPLACE_MAXIMUM_COLUMN_SIZE') ? AKEEBA_REPLACE_MAXIMUM_COLUMN_SIZE : 1048576;

			if (!$regEx && (strlen($original) > $maxColumnSize))
			{
				return self::replaceSerializedPrecariously($original, $from, $to);
			}

			// Smaller serialized columns (or when we have a regex) use a much more robust, slower replacement
			return self::replaceSerialized($original, $from, $to, $regEx);
		}

		// We do not have serialised data. Use a simple, plain text replacement.
		return self::replacePlainText($original, $from, $to, $regEx);
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
			$ret .= sprintf('s:%d:"%s"', $newLength, $content) . ';';

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

	/**
	 * A VERY precarious serialized data replacement
	 *
	 * @param   string  $serialized
	 * @param   string  $from
	 * @param   string  $to
	 *
	 * @return  string
	 */
	protected static function replaceSerializedPrecariously($serialized, $from, $to)
	{
		$pattern = '/s:(\d{1,}):\"(.*)\"/iU';

		return preg_replace_callback($pattern, function ($matches) use ($from, $to) {
			if (strpos($matches[0], $from) === false)
			{
				return $matches[0];
			}

			$replacement = str_replace($from, $to, $matches[2]);

			return sprintf("s:%d:\"%s\"", strlen($replacement), $replacement);
		}, $serialized);
	}

}