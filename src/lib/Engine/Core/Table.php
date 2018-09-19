<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Core;


use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Database\Metadata\Table as TableMeta;
use Akeeba\Replace\Engine\AbstractPart;
use Akeeba\Replace\Logger\LoggerAware;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Timer\TimerInterface;
use Akeeba\Replace\Writer\WriterInterface;

class Table extends AbstractPart
{
	use LoggerAware;

	/**
	 * The driver we are using to connect to our database.
	 *
	 * @var  Driver
	 */
	protected $db = null;

	/**
	 * The writer to use for action SQL file output
	 *
	 * @var  WriterInterface
	 */
	private $outputWriter;

	/**
	 * The writer to use for backup SQL file output
	 *
	 * @var  WriterInterface
	 */
	private $backupWriter;

	/**
	 * The next table row we have to process
	 *
	 * @var  int
	 */
	private $offset = 0;

	/**
	 * The determined batch size of the table
	 *
	 * @var  int
	 */
	private $batch = 1;

	/**
	 * The metadata of the table we are processing
	 *
	 * @var  TableMeta
	 */
	private $meta = null;

	/**
	 * Table constructor.
	 *
	 * @param   TimerInterface   $timer         The timer object that controls us
	 * @param   Driver           $db            The database we are operating against
	 * @param   LoggerInterface  $logger        The logger for our actions
	 * @param   Configuration    $config        The engine configuration
	 * @param   WriterInterface  $outputWriter  The writer for the output SQL file (can be null)
	 * @param   WriterInterface  $backupWriter  The writer for the backup SQL file (can be null)
	 * @param   TableMeta        $tableMeta     The metadata of the table we will be processing
	 */
	public function __construct(TimerInterface $timer, Driver $db, LoggerInterface $logger, Configuration $config, $outputWriter, $backupWriter, TableMeta $tableMeta)
	{
		$this->setLogger($logger);

		$this->db           = $db;
		$this->outputWriter = $outputWriter;
		$this->backupWriter = $backupWriter;
		$this->meta         = $tableMeta;

		parent::__construct($timer, $config);
	}

	protected function prepare()
	{
		// TODO Get meta for columns

		// TODO Run once-per-table callbacks.

		// TODO Filter out columns: text columns

		// TODO Filter out columns: excluded columns

		// TODO Determine optimal batch size
		$this->offset = 0;
		$this->backupWriter  = $this->getOptimumBatchSize($this->meta, $this->config->getMaxBatchSize());

		// TODO Determine set of rows which constitute a primary key

		// TODO If no primary key was determined than ALL columns are my primary key
	}

	protected function process()
	{
		// TODO Get the next batch of rows

		// TODO Iterate every row as long as we have enough time. NOTE: You cannot use a cursor because you need to execute SQL.

			// TODO Iterate columns, run the replacement against them

			// TODO If the row has not been modified continue

			// TODO Get the WHERE clause based on the already determined PK columns

			// TODO Generate backup SQL

			// TODO Write backup SQL

			// TODO Generate action SQL

			// TODO Write action SQL

			// TODO Execute action SQL

			// TODO Update current row number ($this->offset)
	}

	protected function finalize()
	{
		// TODO Log message that we are done
	}

	protected function getOptimumBatchSize(TableMeta $tableMeta, $defaultBatchSize = 1000)
	{
		$averageRowLength = $tableMeta->getAverageRowLength();

		if (empty($averageRowLength))
		{
			// Unknown average row length, use the maximum batch size already configured
			return $defaultBatchSize;
		}

		// That's the average row size as reported by MySQL.
		$avgRow      = str_replace(array(',', '.'), array('', ''), $averageRowLength);
		// The memory available for manipulating data is less than the free memory
		$memoryLimit = $this->getMemoryLimit();
		$memoryLimit = empty($memoryLimit) ? 33554432 : $memoryLimit;
		$usedMemory  = $this->getMemoryUsage();
		$memoryLeft  = 0.75 * ($memoryLimit - $usedMemory);
		// The 3.25 factor is empirical and leans on the safe side.
		$maxRows     = (int) ($memoryLeft / (3.25 * $avgRow));

		return max(1, min($maxRows, $defaultBatchSize));
	}

	/**
	 * Get the PHP memory limit in bytes
	 *
	 * @return int|null  Memory limit in bytes or null if we can't figure it out.
	 */
	protected function getMemoryLimit()
	{
		if (!function_exists('ini_get'))
		{
			return null;
		}

		$memLimit = ini_get("memory_limit");

		if ((is_numeric($memLimit) && ($memLimit < 0)) || !is_numeric($memLimit))
		{
			// A negative memory limit means no memory limit, see http://php.net/manual/en/ini.core.php#ini.memory-limit
			$memLimit = 0;
		}

		$memLimit = $this->humanToIntegerBytes($memLimit);

		return $memLimit;
	}

	/**
	 * Converts a human formatted size to integer representation of bytes,
	 * e.g. 1M to 1024768
	 *
	 * @param   string  $setting  The value in human readable format, e.g. "1M"
	 *
	 * @return  integer  The value in bytes
	 */
	protected function humanToIntegerBytes($setting)
	{
		$val = trim($setting);
		$last = strtolower($val{strlen($val) - 1});

		if (is_numeric($last))
		{
			return $setting;
		}

		switch ($last)
		{
			case 't':
				$val *= 1024;
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return (int) $val;
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
	protected function getMemoryUsage()
	{
		return memory_get_usage();
	}
}