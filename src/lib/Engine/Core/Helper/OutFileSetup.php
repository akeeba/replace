<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Core\Helper;

use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Logger\FileLogger;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Logger\NullLogger;
use Akeeba\Replace\Writer\FileWriter;
use Akeeba\Replace\Writer\NullWriter;
use Akeeba\Replace\Writer\WriterInterface;
use DateTime;
use DateTimeZone;
use Exception;

/**
 * An object to help setting up output files (log, backup SQL, output SQL), returning the relevant objects.
 *
 * This is to be used by the user interfaces to construct the dependencies which are passed to core Engine Parts.
 *
 * @package  Akeeba\Replace\Engine\Core\Helper
 */
class OutFileSetup
{
	/**
	 * The time which will be used for variable replacement in file names
	 *
	 * @var  DateTime
	 */
	private $dateTime;

	/**
	 * The time zone which will be used for variable replacement in file names
	 *
	 * @var  DateTimeZone
	 */
	private $timeZone;

	/**
	 * OutFileSetup constructor.
	 *
	 * @param   DateTime|string $dateTime
	 * @param   DateTimeZone|string $timeZone
	 */
	public function __construct($dateTime = 'now', $timeZone = 'UTC')
	{
		if (!is_object($timeZone))
		{
			try
			{
				$timeZone = new DateTimeZone($timeZone);
			}
			catch (Exception $e)
			{
				$timeZone = new DateTimeZone('UTC');
			}
		}

		$this->timeZone = $timeZone;

		if (!is_object($dateTime))
		{
			try
			{
				$dateTime = new DateTime($dateTime, 'UTC');
			}
			catch (Exception $e)
			{
				$dateTime = new DateTime('now', 'UTC');
			}
		}

		$this->dateTime = $dateTime;
	}

	/**
	 * Get a timestamp in the local time zone (set up in the constructor).
	 *
	 * The $dateTime parameter can be:
	 * - 'now'                : Set to the current timestamp
	 * - an integer           : Set to the UNIX timestamp expressed by the integer
	 * - a DateTime object    : Used as-is
	 * - anything else / null : Use the DateTime given in the object constructor (fixed point in time)
	 *
	 * @param   string                $format    Date/time format (see date())
	 * @param   string|null|DateTime  $dateTime  The date and time to format. See above.
	 *
	 * @return  string
	 */
	public function getLocalTimeStamp($format = 'Y-m-d H:i:s', $dateTime = null)
	{
		if ($dateTime == 'now')
		{
			$utcTimeZone = new DateTimeZone('UTC');
			$dateTime    = new DateTime('now', $utcTimeZone);
		}
		elseif (is_int($dateTime))
		{
			$utcTimeZone = new DateTimeZone('UTC');
			$dateTime    = new DateTime($dateTime, $utcTimeZone);
		}
		elseif (!is_object($dateTime) || !($dateTime instanceof DateTime))
		{
			$dateTime = $this->dateTime;
		}

		$dateNow = clone $dateTime;
		$dateNow->setTimezone($this->timeZone);

		return $dateNow->format($format);

	}

	/**
	 * Return the file naming variables.
	 *
	 * @return  array
	 */
	public function getVariables()
	{
		/**
		 * Time components. Expressed in whatever timezone the Platform decides to use.
		 */
		// Raw timezone, e.g. "EEST"
		$rawTz     = $this->getLocalTimeStamp("T");
		// Filename-safe timezone, e.g. "eest". Note the lowercase letters.
		$fsSafeTZ  = strtolower(str_replace(array(' ', '/', ':'), array('_', '_', '_'), $rawTz));

		return [
			'[DATE]'             => $this->getLocalTimeStamp("Ymd"),
			'[YEAR]'             => $this->getLocalTimeStamp("Y"),
			'[MONTH]'            => $this->getLocalTimeStamp("m"),
			'[DAY]'              => $this->getLocalTimeStamp("d"),
			'[TIME]'             => $this->getLocalTimeStamp("His"),
			'[TIME_TZ]'          => $this->getLocalTimeStamp("His") . $fsSafeTZ,
			'[WEEK]'             => $this->getLocalTimeStamp("W"),
			'[WEEKDAY]'          => $this->getLocalTimeStamp("l"),
			'[GMT_OFFSET]'       => $this->getLocalTimeStamp("O"),
			'[TZ]'               => $fsSafeTZ,
			'[TZ_RAW]'           => $rawTz,
		];
	}

	/**
	 * Replace the variables in a given string.
	 *
	 * @param   string  $input       The string to replace variables in
	 * @param   array   $additional  Any additional replacements to make
	 *
	 * @return  string
	 *
	 * @codeCoverageIgnore
	 */
	public function replaceVariables($input, array $additional = [])
	{
		$variables = $this->getVariables();
		$variables = array_merge($variables, $additional);

		return str_replace(array_keys($variables), array_values($variables), $input);
	}

	/**
	 * Create a new output SQL file writer object based on the file path set up in the configuration.
	 *
	 * @param   Configuration  $config      The engine configuration
	 * @param   bool           $reset       Should I delete existing files by that name?
	 * @param   array          $additional  Any additional replacements to make
	 *
	 * @return  WriterInterface
	 */
	public function makeOutputWriter(Configuration $config, $reset = true, array $additional = [])
	{
		$filePath = $config->getOutputSQLFile();

		if (empty($filePath))
		{
			return new NullWriter('');
		}

		$filePath = $this->replaceVariables($filePath, $additional);

		return new FileWriter($filePath, $reset);
	}

	/**
	 * Create a new backup SQL file writer object based on the file path set up in the configuration.
	 *
	 * @param   Configuration  $config      The engine configuration
	 * @param   bool           $reset       Should I delete existing files by that name?
	 * @param   array          $additional  Any additional replacements to make
	 *
	 * @return  WriterInterface
	 */
	public function makeBackupWriter(Configuration $config, $reset = true, array $additional = [])
	{
		$filePath = $config->getBackupSQLFile();

		if (empty($filePath))
		{
			return new NullWriter('');
		}

		$filePath = $this->replaceVariables($filePath, $additional);

		return new FileWriter($filePath, $reset);
	}

	/**
	 * Create a new logger object based on the log file path set up in the configuration. A null logger is returned if
	 * the log path is empty.
	 *
	 * @param   Configuration  $config      The engine configuration
	 * @param   bool           $reset       Should I delete existing files by that name?
	 * @param   array          $additional  Any additional replacements to make
	 *
	 * @return  LoggerInterface
	 */
	public function makeLogger(Configuration $config, $reset = true, array $additional = [])
	{
		$filePath = $config->getLogFile();

		if (empty($filePath))
		{
			return new NullLogger();
		}

		$filePath  = $this->replaceVariables($filePath, $additional);
		$logWriter = new FileWriter($filePath, $reset);
		$logger    = new FileLogger($logWriter);

		$logger->setMinimumSeverity($config->getMinLogLevel());

		return $logger;
	}
}