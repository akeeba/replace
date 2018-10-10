<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\Model;

use Akeeba\Replace\WordPress\MVC\Model\DataModel;
use wpdb;

/**
 * Database-aware model for the #__akeebareplace_jobs table which lists all the replacement jobs which have executed.
 *
 * The fields in the table are as follows:
 *
 * id           automatically incrementing integer key
 * description  description for this job
 * options      serialized array with the Configuration options
 * created_on   when this row was created
 * run_on       last time we ran this job
 *
 * @package  Akeeba\Replace\WordPress\Model
 */
class Job extends DataModel
{
	public function __construct(wpdb $db)
	{
		global $wpdb;

		$this->tableName = $wpdb->prefix . 'akeebareplace_jobs';
		$this->pkName    = 'id';

		parent::__construct($db);
	}
}