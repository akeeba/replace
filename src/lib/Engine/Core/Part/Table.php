<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Core\Part;


use Akeeba\Replace\Database\DatabaseAware;
use Akeeba\Replace\Database\DatabaseAwareInterface;
use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Database\Metadata\Column;
use Akeeba\Replace\Database\Metadata\Table as TableMeta;
use Akeeba\Replace\Engine\AbstractPart;
use Akeeba\Replace\Engine\Core\BackupWriterAware;
use Akeeba\Replace\Engine\Core\BackupWriterAwareInterface;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\ConfigurationAware;
use Akeeba\Replace\Engine\Core\ConfigurationAwareInterface;
use Akeeba\Replace\Engine\Core\Filter\Column\FilterInterface;
use Akeeba\Replace\Engine\Core\Helper\MemoryInfo;
use Akeeba\Replace\Engine\Core\OutputWriterAware;
use Akeeba\Replace\Engine\Core\OutputWriterAwareInterface;
use Akeeba\Replace\Engine\PartInterface;
use Akeeba\Replace\Logger\LoggerAware;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Timer\TimerInterface;
use Akeeba\Replace\Writer\WriterInterface;

/**
 * An Engine Part to process the contents of database tables
 *
 * @package Akeeba\Replace\Engine\Core\Part
 */
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
	 * Hard-coded list of table filter classes. This is for my convenience.
	 *
	 * @var  array
	 */
	private $filters = [
		'Akeeba\\Replace\\Engine\\Core\\Filter\\Column\\NonText',
		'Akeeba\\Replace\\Engine\\Core\\Filter\\Column\\UserFilters',
	];

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
	protected $offset = 0;

	/**
	 * The determined batch size of the table
	 *
	 * @var  int
	 */
	protected $batch = 1;

	/**
	 * The metadata of the table we are processing
	 *
	 * @var  TableMeta
	 */
	protected $tableMeta = null;

	/**
	 * The metadata for the columns of the table
	 *
	 * @var  Column[]
	 */
	protected $columnsMeta = [];

	/**
	 * The names of the columns which constitute the table's primary key
	 *
	 * @var  string[]
	 */
	protected $primaryKeyColumns = [];

	/**
	 * The names of the columns to which we will be applying replacements
	 *
	 * @var  string[]
	 */
	protected $replaceableColumns = [];

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

		$this->tableMeta  = $tableMeta;
		$this->memoryInfo = $memInfo;

		parent::__construct($timer, $config);
	}

	protected function prepare()
	{
		// Get meta for columns
		$this->columnsMeta = $this->getDbo()->getColumnsMeta($this->tableMeta->getName());

		// TODO Run once-per-table callbacks.

		$this->getLogger()->debug('Filtering the columns list');
		$this->replaceableColumns = $this->applyFilters($this->tableMeta, $this->columnsMeta, $this->filters);

		/**
		 * Are there no text columns left? This can happen in two ways:
		 *
		 * 1. Only non-text columns on the table, e.g. a glue table in a many-to-many table relationship
		 * 2. All text columns were filtered out by text filters
		 *
		 * In this case we mark ourselves as post-run and terminate early. Note that we use STATE_POSTRUN, not
		 * STATE_FINALIZED. That's because the call the nextState() in the abstract superclass will do the transition
		 * for us.
		 */
		if (empty($this->replaceableColumns))
		{
			$this->getLogger()->info(sprintf('Skipping table %s -- It does not have any text columns I can replace data into.', $this->tableMeta->getName()));
			$this->state = PartInterface::STATE_POSTRUN;
		}

		// Log columns to replace
		$this->getLogger()->debug(sprintf('Table %s replaceable columns: %s', $this->tableMeta->getName(), implode(', ', $this->replaceableColumns)));

		// Determine optimal batch size
		$memoryLimit      = $this->memoryInfo->getMemoryLimit();
		$usedMemory       = $this->memoryInfo->getMemoryUsage();
		$defaultBatchSize = $this->getConfig()->getMaxBatchSize();
		$this->batch      = $this->getOptimumBatchSize($this->tableMeta, $memoryLimit, $usedMemory, $defaultBatchSize);
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
	 * Apply the hard-coded list of column filters against the provided columns list and return a filtered list of
	 * strings, consisting of the column names which will we be replacing into.
	 *
	 * @param   TableMeta  $tableMeta    The metadata of the table we are filtering columns for
	 * @param   Column[]   $columnsMeta  The columns metadata we will be filtering
	 * @param   string[]   $filters      The filters to apply
	 *
	 * @return  string[]
	 */
	protected function applyFilters(TableMeta $tableMeta, array $columnsMeta, array $filters)
	{
		$allColumns = array_merge($columnsMeta);

		foreach ($filters as $class)
		{
			if (!class_exists($class))
			{
				$this->addWarningMessage(sprintf("Filter class “%s” not found. Is your installation broken?", $class));

				continue;
			}

			if (!in_array('Akeeba\\Replace\\Engine\\Core\\Filter\\Column\\FilterInterface', class_implements($class)))
			{
				$this->addWarningMessage(sprintf("Filter class “%s” is not a valid column filter. Is your installation broken?", $class));

				continue;
			}

			/** @var FilterInterface $o */
			$o = new $class($this->getLogger(), $this->getDomain(), $this->getConfig());
			$allColumns = $o->filter($tableMeta, $allColumns);
		}

		$ret = [];

		if (empty($allColumns))
		{
			return $ret;
		}

		/** @var Column $column */
		foreach ($allColumns as $column)
		{
			$ret[] = $column->getColumnName();
		}

		return $ret;

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
	protected function getOptimumBatchSize(TableMeta $tableMeta, $memoryLimit, $usedMemory, $defaultBatchSize = 1000)
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