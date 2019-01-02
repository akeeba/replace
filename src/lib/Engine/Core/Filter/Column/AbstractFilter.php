<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core\Filter\Column;


use Akeeba\Replace\Database\DatabaseAware;
use Akeeba\Replace\Database\DatabaseAwareInterface;
use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\ConfigurationAware;
use Akeeba\Replace\Engine\Core\ConfigurationAwareInterface;
use Akeeba\Replace\Logger\LoggerAware;
use Akeeba\Replace\Logger\LoggerAwareInterface;
use Akeeba\Replace\Logger\LoggerInterface;

/**
 * Abstract implementation for a columns list filter
 *
 * @package Akeeba\Replace\Engine\Core\Filter\Column
 */
abstract class AbstractFilter implements FilterInterface, LoggerAwareInterface, DatabaseAwareInterface,
	ConfigurationAwareInterface
{
	use LoggerAware;
	use DatabaseAware;
	use ConfigurationAware;

	/**
	 * AbstractFilter constructor.
	 *
	 * @param   LoggerInterface  $logger   The logger used to log our actions
	 * @param   Driver           $db       The database connection object
	 * @param   Configuration    $config   The engine configuration
	 */
	public function __construct(LoggerInterface $logger, Driver $db, Configuration $config)
	{
		$this->setLogger($logger);
		$this->setDriver($db);
		$this->setConfig($config);
	}

}