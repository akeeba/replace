<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Replace\Engine\Core\Action\Table;


use Akeeba\Replace\Database\DatabaseAware;
use Akeeba\Replace\Database\DatabaseAwareInterface;
use Akeeba\Replace\Database\Driver;
use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\Engine\Core\ConfigurationAware;
use Akeeba\Replace\Engine\Core\ConfigurationAwareInterface;
use Akeeba\Replace\Logger\LoggerAware;
use Akeeba\Replace\Logger\LoggerAwareInterface;
use Akeeba\Replace\Logger\LoggerInterface;

abstract class AbstractAction implements ActionInterface, DatabaseAwareInterface, LoggerAwareInterface,
	ConfigurationAwareInterface
{
	use DatabaseAware;
	use LoggerAware;
	use ConfigurationAware;

	public function __construct(Driver $db, LoggerInterface $logger, Configuration $config)
	{
		$this->setDriver($db);
		$this->setLogger($logger);
		$this->setConfig($config);
	}


}