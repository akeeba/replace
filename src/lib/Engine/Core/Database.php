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
use Akeeba\Replace\Engine\AbstractPart;
use Akeeba\Replace\Logger\LoggerAware;
use Akeeba\Replace\Logger\LoggerInterface;
use Akeeba\Replace\Timer\TimerInterface;

/**
 * An Engine Part which iterates a database for tables
 *
 * @package Akeeba\Replace\Engine\Part
 */
class Database extends AbstractPart
{
	use LoggerAware;

	/**
	 * The driver we are using to connect to our database.
	 *
	 * @var  Driver
	 */
	protected $db = null;

	/**
	 * Overloaded constructor.
	 *
	 * @param TimerInterface  $timer
	 * @param Driver          $db
	 * @param LoggerInterface $logger
	 * @param array           $params
	 */
	public function __construct(TimerInterface $timer, Driver $db, LoggerInterface $logger, array $params)
	{
		$this->db = $db;

		$this->setLogger($logger);

		parent::__construct($timer, $params);

		// TODO How do I best get the callbacks to allow testing? Create them in the part or get them as dependencies? Or both?
	}

	protected function prepare()
	{
		// TODO Setup a file writer for the output SQL file.
		// TODO Setup a file writer for the backup SQL file.
		// TODO Run once-per-database callbacks.
		// TODO Get the list of tables.
		// TODO Filter the tables
	}

	protected function process()
	{
		// TODO Iterate each of the tables. Will have to create a table iterator part and step it.
	}

	protected function finalize()
	{
		// TODO: Implement finalize() method.
	}

}