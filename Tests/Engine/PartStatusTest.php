<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

/**
 * Created by PhpStorm.
 * User: nicholas
 * Date: 14/9/2018
 * Time: 6:33 μμ
 */

namespace Akeeba\Replace\Tests\Engine;

use Akeeba\Replace\Engine\AbstractPart;
use Akeeba\Replace\Engine\PartStatus;
use Akeeba\Replace\Tests\Stubs\Engine\AbstractPartStub;
use Akeeba\Replace\Timer\Timer;

class PartStatusTest extends \PHPUnit_Framework_TestCase
{

	public function test__constructEmpty()
	{
		$emptyParams = [];
		$dummy = new PartStatus($emptyParams);

		$this->assertSame(false, $this->getObjectAttribute($dummy, 'done'), 'Default "done" must be false');
		$this->assertSame('', $this->getObjectAttribute($dummy, 'domain'), 'Default "domain" must be an empty string');
		$this->assertSame('', $this->getObjectAttribute($dummy, 'step'), 'Default "step" must be an empty string');
		$this->assertSame('', $this->getObjectAttribute($dummy, 'substep'), 'Default "substep" must be an empty string');
		$this->assertSame(null, $this->getObjectAttribute($dummy, 'error'), 'Default "error" must be null');
		$this->assertSame([], $this->getObjectAttribute($dummy, 'warnings'), 'Default "warnings" must be null');
	}

	/**
	 * @param   mixed  $param
	 * @param   bool   $expected
	 *
	 * @dataProvider \Akeeba\Replace\Tests\Engine\PartStatusProvider::test__constructWithDoneProvider()
	 */
	public function test__constructWithDone($param, $expected)
	{
		$message = sprintf("Passing 'Done' => %s in the params must result in \$done === %s in the constructed object", print_r($param, true), $expected ? 'true' : 'false');

		$parameters = [
			'Done' => $param
		];

		$dummy = new PartStatus($parameters);
		$this->assertSame($expected, $this->getObjectAttribute($dummy, 'done'), $message);
	}

	/**
	 * @param   mixed  $param
	 * @param   bool   $expected
	 *
	 * @dataProvider \Akeeba\Replace\Tests\Engine\PartStatusProvider::test__constructWithDoneProvider()
	 *
	 * Since HasRun is the polar opposite to Done we are using the same data provider and looking for the *opposite*
	 * result than what was expected there.
	 */
	public function test__constructWithHasRun($param, $expected)
	{
		$message = sprintf("Passing 'HasRun' => %s in the params must result in \$done === %s in the constructed object", print_r($param, true), !$expected ? 'true' : 'false');

		$parameters = [
			'HasRun' => $param
		];

		$dummy = new PartStatus($parameters);
		$this->assertSame(!$expected, $this->getObjectAttribute($dummy, 'done'), $message);
	}

	/**
	 * @param $param
	 * @param $expected
	 *
	 * @dataProvider \Akeeba\Replace\Tests\Engine\PartStatusProvider::test__constructWithErrorProvider()
	 *
	 * @covers \Akeeba\Replace\Engine\PartStatus::setError
	 */
	public function test__constructWithError($param, $expected)
	{
		$parameters = [
			'Error' => $param
		];

		$dummy = new PartStatus($parameters);
		$this->assertEquals($expected, $this->getObjectAttribute($dummy, 'error'));
	}

	/**
	 * @param $param
	 * @param $expected
	 *
	 * @dataProvider \Akeeba\Replace\Tests\Engine\PartStatusProvider::test__constructWithWarningsProvider()
	 *
	 * @covers \Akeeba\Replace\Engine\PartStatus::setWarnings
	 * @covers \Akeeba\Replace\Engine\PartStatus::addWarning
	 */
	public function test__constructWithWarnings($param, $expected)
	{
		$parameters = [
			'Warnings' => $param
		];

		$dummy = new PartStatus($parameters);
		$this->assertEquals($expected, $this->getObjectAttribute($dummy, 'warnings'));
	}


	public function testFromPart()
	{
		$timer = $this->getMockBuilder(Timer::class)->getMock();
		/** @var AbstractPart $part */
		$part   = new AbstractPartStub($timer, []);

		$this->callObjectMethod($part, 'setDomain', 'foo');
		$this->callObjectMethod($part, 'setStep', 'bar');
		$this->callObjectMethod($part, 'setSubstep', 'bat');
		$part->setErrorMessage('Foo bar baz');
		$part->addWarningMessage('Foo');
		$part->addWarningMessage('Bar');
		$part->addWarningMessage('Baz');

		$status = PartStatus::fromPart($part);

		$this->assertFalse($status->isDone());
		$this->assertEquals($status->getDomain(), 'foo');
		$this->assertEquals($status->getStep(), 'bar');
		$this->assertEquals($status->getSubstep(), 'bat');
		$this->assertSame($status->getError(), $part->getError());
		$this->assertSame($status->getWarnings(), $part->getWarnings());
	}

	public function testToArray()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	private function callObjectMethod($object, $method, $params)
	{
		$refObj = new \ReflectionObject($object);
		$refMethod = $refObj->getMethod($method);
		$refMethod->setAccessible(true);
		$refMethod->invoke($object, $params);
	}
}
