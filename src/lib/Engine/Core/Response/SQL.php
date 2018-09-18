<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
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
	 * @var  Query[]
	 */
	private $actionQueries = [];

	/**
	 * The query to undo the action taken by the actionQuery (used for backups)
	 *
	 * @var  Query[]
	 */
	private $restorationQueries = [];

	/**
	 * SQLResponse constructor.
	 *
	 * @param   Query[] $actionQueries
	 * @param   Query[] $restorationQueries
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
	 *
	 * @codeCoverageIgnore
	 */
	public function hasActionQueries()
	{
		return !empty($this->actionQueries);
	}

	/**
	 * Does this response define restoration queries?
	 *
	 * @return  bool
	 *
	 * @codeCoverageIgnore
	 */
	public function hasRestorationQueries()
	{
		return !empty($this->restorationQueries);
	}

	/**
	 * Get the action queries.
	 *
	 * @return  Query[]
	 *
	 * @codeCoverageIgnore
	 */
	public function getActionQueries()
	{
		return $this->actionQueries;
	}

	/**
	 * Get the restoration query.
	 *
	 * @return  Query[]
	 *
	 * @codeCoverageIgnore
	 */
	public function getRestorationQueries()
	{
		return $this->restorationQueries;
	}
}