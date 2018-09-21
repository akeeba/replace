<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Core;


use Akeeba\Replace\Database\DatabaseAware;
use Akeeba\Replace\Database\DatabaseAwareInterface;
use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Database\Metadata\Table as TableMeta;
use Akeeba\Replace\Engine\AbstractPart;
use Akeeba\Replace\Engine\Core\Helper\MemoryInfo;
use Akeeba\Replace\Logger\LoggerAware;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Timer\TimerInterface;
use Akeeba\Replace\Writer\WriterInterface;

class Table extends AbstractPart implements
	ConfigurationAwareInterface,
	DatabaseAwareInterface,
	OutputWriterAwareInterface,
	BackupWriterAwareInterface
{
	use LoggerAware;
	use DatabaseAware;
	use ConfigurationAware;
	use OutputWriterAware;
	use BackupWriterAware;

	/**
	 * The memory information helper, used to take decisions based on the available PHP memory
	 *
	 * @var  MemoryInfo
	 */
	protected $memoryInfo = null;

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
	 * @param   MemoryInfo       $memInfo       Memory info object, used for determining optimum batch size
	 */
	public function __construct(TimerInterface $timer, Driver $db, LoggerInterface $logger, Configuration $config, WriterInterface $outputWriter, WriterInterface $backupWriter, TableMeta $tableMeta, MemoryInfo $memInfo)
	{
		$this->setLogger($logger);
		$this->setDriver($db);
		$this->setConfig($config);
		$this->setOutputWriter($outputWriter);
		$this->setBackupWriter($backupWriter);

		$this->meta         = $tableMeta;
		$this->memoryInfo   = $memInfo;

		parent::__construct($timer, $config);
	}

	protected function prepare()
	{
		// TODO Get meta for columns

		// TODO Run once-per-table callbacks.

		// TODO Filter out columns: text columns

		// TODO Filter out columns: excluded columns

		// TODO Determine optimal batch size
		$memoryLimit      = $this->memoryInfo->getMemoryLimit();
		$usedMemory       = $this->memoryInfo->getMemoryUsage();
		$defaultBatchSize = $this->getConfig()->getMaxBatchSize();
		$this->batch      = $this->getOptimumBatchSize($this->meta, $memoryLimit, $usedMemory, $defaultBatchSize);
		$this->offset     = 0;

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

	/**
	 * Returns the optimum batch size for a table. This depends on the average row size of the table and the available
	 * PHP memory. If we have plenty of memory (or no limit) we are going to use the default batch size. The returned
	 * batch size can never be larger than the default batch size.
	 *
	 * @param   TableMeta  $tableMeta         The metadata of the table. We are going to use the average row size.
	 * @param   int        $memoryLimit       How much PHP memory is available, 0 for no limit
	 * @param   int        $usedMemory        How much PHP memory is used, in bytes
	 * @param   int        $defaultBatchSize  The default (and maximum) batch size
	 *
	 * @return  int
	 */
	public function getOptimumBatchSize(TableMeta $tableMeta, $memoryLimit, $usedMemory, $defaultBatchSize = 1000)
	{
		// No memory limit? Return the default batch size
		if ($memoryLimit <= 0)
		{
			return $defaultBatchSize;
		}

		// Get the average row length. If it's unknown use the default batch size.
		$averageRowLength = $tableMeta->getAverageRowLength();

		if (empty($averageRowLength))
		{
			return $defaultBatchSize;
		}

		// Make sure the average row size is an integer
		$avgRow = str_replace([',', '.'], ['', ''], $averageRowLength);
		$avgRow = (int) $avgRow;

		// If the average row size is not a positive integer use the default batch size.
		if ($avgRow <= 0)
		{
			return $defaultBatchSize;
		}

		// The memory available for manipulating data is less than the free memory. The 0.75 factor is empirical.
		$memoryLeft  = 0.75 * ($memoryLimit - $usedMemory);

		// This should never happen. I will return the default batch size and brace for impact: crash imminent!
		if ($memoryLeft <= 0)
		{
			$this->getLogger()->debug('Cannot determine optimal row size: PHP reports that its used memory is larger than the configured memory limit. This is NOT normal! I expect PHP to crash soon with an out of memory Fatal Error.');

			return $defaultBatchSize;
		}

		// The 3.25 factor is empirical and leans on the safe side.
		$maxRows = (int) ($memoryLeft / (3.25 * $avgRow));

		return max(1, min($maxRows, $defaultBatchSize));
	}


}