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
	 * @param   bool    $regEx     Treat $from as Regular Expression
	 *
	 * @return  string
	 */
	protected static function replaceSerialized($serialized, $from, $to, $regEx = false)
	{
		$pattern  = '/s:(\d{1,}):\"/iU';
		$exploded = preg_split($pattern, $serialized, -1, PREG_SPLIT_DELIM_CAPTURE);

		$lastLen = null;

		$exploded = array_map(function ($piece) use (&$lastLen, $from, $to, $regEx) {
			// Numeric pieces are the string lengths
			if (is_numeric($piece))
			{
				$lastLen = (int) $piece;

				return '';
			}

			// If we have not encountered a string length we are processing the first chunk of the serialised data
			if (is_null($lastLen))
			{
				return $piece;
			}

			// I expect $lastLen + 2 characters (double quote, string, double quote). Break the piece in two parts.
			$toReplace   = substr($piece, 0, $lastLen);
			$theRestOfIt = substr($piece, $lastLen + 1);

			/**
			 * Replace data in the first part.
			 *
			 * We go through self::replace() to catch the case where a serialized object/array contains a string which
			 * is, in its turn, serialized data. Serialized data inside a string in serialized data (much like the
			 * dream-world-inside-a-dream-world depicted in the movie Inception) is something that reeks of horrid
			 * architecture bit it's not uncommon in the WordPress world.
			 */
			$toReplace = self::replace($toReplace, $from, $to, $regEx);
			$newLength = function_exists('mb_strlen') ? mb_strlen($toReplace, 'ASCII') : strlen($toReplace);

			// New piece is s:newLength:"replacedString"TheRestOfIt
			$lastLen = null;

			return 's:' . $newLength . ':"' . $toReplace . '"' . $theRestOfIt;
		}, $exploded);

		// Remove the empty strings
		return implode("", $exploded);
	}

	/**
	 * Does this string look like PHP serialised data? Please note that this is a quick pre-test. It's not 100% correct
	 * but it should work in all significant real-world cases.
	 *
	 * @param   string  $string The string to test
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

}