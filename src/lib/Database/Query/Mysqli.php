<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Database\Query;

use Akeeba\Replace\Database;

/**
 * Query Building Class for databases using the MySQLi connector.
 *
 * @codeCoverageIgnore
 */
class Mysqli extends Database\Query implements Database\QueryLimitable
{
	use LimitAware;
}
