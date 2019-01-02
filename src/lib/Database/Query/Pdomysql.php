<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Database\Query;


use Akeeba\Replace\Database\QueryLimitable;

/**
 * Query builder class for databases using the PDO connector
 *
 * @codeCoverageIgnore
 */
class Pdomysql extends Pdo implements QueryLimitable
{
	use LimitAware;
}