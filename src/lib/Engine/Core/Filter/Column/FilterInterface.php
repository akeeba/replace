<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core\Filter\Column;


use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Database\Metadata\Column;
use Akeeba\Replace\Database\Metadata\Table;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Logger\LoggerInterface;

/**
 * Interface to a columns list filter
 *
 * @package Akeeba\Replace\Engine\Core\Filter\Column
 */
interface FilterInterface
{
	/**
	 * FilterInterface  constructor.
	 *
	 * @param   LoggerInterface  $logger   The logger used to log our actions
	 * @param   Driver           $db       The database connection object
	 * @param   Configuration    $config   The engine configuration
	 */
	public function __construct(LoggerInterface $logger, Driver $db, Configuration $config);

	/**
	 * Filter the columns list, returning the filtered result
	 *
	 * @param   Table     $table    The table where the columns belong to
	 * @param   Column[]  $columns  The columns we are filtering
	 *
	 * @return  array
	 */
	public function filter(Table $table, array $columns);
}