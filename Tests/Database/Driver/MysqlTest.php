<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Database\Driver;

class MysqlTest extends MysqliTest
{
	/**
	 * @var   string  The name of the database driver to instantiate
	 */
	protected static $driverName = 'mysql';

	/**
	 * Test isSupported method.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function testIsSupported()
	{
		self::assertThat(\Akeeba\Replace\Database\Driver\Mysql::isSupported(), $this->isTrue(), __LINE__);
	}

}
