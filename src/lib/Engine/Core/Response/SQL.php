<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core\Response;

use Akeeba\Replace\Database\Query;

/**
 * Describes the immutable response returned by a database or table Action object.
 *
 * @package Akeeba\Replace\Engine\Core
 */
class SQL
{
	/**
	 * The query to perform an action.
	 *
	 * @var  string[]
	 */
	private $actionQueries = [];

	/**
	 * The query to undo the action taken by the actionQuery (used for backups)
	 *
	 * @var  string[]
	 */
	private $restorationQueries = [];

	/**
	 * SQLResponse constructor.
	 *
	 * @param   string[] $actionQueries
	 * @param   string[] $restorationQueries
	 */
	public function __construct($actionQueries, $restorationQueries)
	{
		$this->actionQueries      = is_array($actionQueries) ? $actionQueries : null;
		$this->restorationQueries = is_array($restorationQueries) ? $restorationQueries : null;
	}

	/**
	 * Does this response define action queries?
	 *
	 * @return  bool
	 */
	public function hasActionQueries()
	{
		return !empty($this->actionQueries);
	}

	/**
	 * Does this response define restoration queries?
	 *
	 * @return  bool
	 */
	public function hasRestorationQueries()
	{
		return !empty($this->restorationQueries);
	}

	/**
	 * Get the action queries.
	 *
	 * @return  string[]
	 */
	public function getActionQueries()
	{
		return $this->actionQueries;
	}

	/**
	 * Get the restoration query.
	 *
	 * @return  string[]
	 */
	public function getRestorationQueries()
	{
		return $this->restorationQueries;
	}
}