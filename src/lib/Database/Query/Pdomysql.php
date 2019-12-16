<?php
/**
 * ANGIE - The site restoration script for backup archives created by Akeeba Backup and Akeeba Solo
 *
 * @package   angie
 * @copyright Copyright (c)2009-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
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