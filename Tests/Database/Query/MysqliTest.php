<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Database\Query;

use Akeeba\Replace\Database\Query\Mysqli;
use Akeeba\Replace\Tests\Database\DriverTestCase;

class MysqliTest extends DriverTestCase
{
	/**
	 * @var   string  The name of the database driver to instantiate
	 */
	protected static $driverName = 'mysqli';

	/**
	 * Test for LIMIT and OFFSET clause.
	 *
	 * @return  void
	 *
	 * @covers \Akeeba\Replace\Database\Query\Mysqli::setLimit
	 */
	public function testSetLimitAndOffset()
	{
		$driver = static::getDriver();

		$q = new Mysqli($driver);
		$q->setLimit('5', '10');

		$this->assertThat(
			trim($this->getObjectAttribute($q, 'limit')),
			$this->equalTo('5'),
			'Tests limit was set correctly.'
		);

		$this->assertThat(
			trim($this->getObjectAttribute($q, 'offset')),
			$this->equalTo('10'),
			'Tests offset was set correctly.'
		);
	}

	/**
	 * Tests the \Awf\Database\Query\Mysqli::processLimit method.
	 *
	 * @return  void
	 *
	 * @covers \Akeeba\Replace\Database\Query\Mysqli::processLimit
	 */
	public function testProcessLimit()
	{
		$driver = static::getDriver();
		$q      = new Mysqli($driver);

		$this->assertThat(
			trim($q->processLimit('SELECT foo FROM bar', 5, 10)),
			$this->equalTo('SELECT foo FROM bar LIMIT 10, 5'),
			'Tests rendered value.'
		);
	}

	/**
	 * Test for "concatenate" words.
	 *
	 * @return  void
	 */
	public function testConcatenate()
	{
		$driver = static::getDriver();
		$q      = new Mysqli($driver);

		$this->assertThat(
			$q->concatenate(array('foo', 'bar')),
			$this->equalTo('CONCAT(foo,bar)'),
			'Tests without separator.'
		);

		$this->assertThat(
			$q->concatenate(array('foo', 'bar'), ' and '),
			$this->equalTo("CONCAT_WS(' and ', foo, bar)"),
			'Tests without separator.'
		);
	}
}
