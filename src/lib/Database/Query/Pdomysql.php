<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Database\Query;


use Akeeba\Replace\Database\QueryLimitable;

class Pdomysql extends Pdo implements QueryLimitable
{
	use LimitAware;
}