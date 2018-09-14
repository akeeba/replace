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
 * Time: 6:09 μμ
 */

namespace Akeeba\Replace\Tests\Engine;

use Akeeba\Replace\Engine\AbstractPart;
use Akeeba\Replace\Engine\PartInterface;

class AbstractPartTest extends \PHPUnit_Framework_TestCase
{
	public function test__construct()
	{
		// Fake timer for testing
		$timer = $this->createMock('Akeeba\Replace\Timer\Timer');
		// Fake parameters
		$params = ['foo' => 'bar', 'baz' => 'bat'];
		// Create a dummy object from the abstract class
		$dummy = $this->getMockForAbstractClass('Akeeba\Replace\Engine\AbstractPart', [
			$timer, $params,
		]);

		// Make sure we got the correct object type
		$this->assertInstanceOf('Akeeba\Replace\Engine\AbstractPart', $dummy, 'TEST ERROR: We do not have the correct class.');

		$actualTimer  = $this->getObjectAttribute($dummy, 'timer');
		$actualState  = $this->getObjectAttribute($dummy, 'state');
		$actualParams = $this->getObjectAttribute($dummy, 'parameters');

		$this->assertInstanceOf('Akeeba\Replace\Timer\TimerInterface', $actualTimer, 'The Timer must be set by the constructor');
		$this->assertSame($timer, $actualTimer, 'The actual Timer object must be set by the constructor');
		$this->assertEquals(PartInterface::STATE_INIT, $actualState, 'The initial state must be STATE_INIT');
		$this->assertSame($params, $actualParams, 'The parameters must be set by the constructor');
	}

	public function testSetup()
	{
		// Fake timer for testing
		$timer = $this->createMock('Akeeba\Replace\Timer\Timer');
		// Fake parameters
		$params = ['foo' => 'bar', 'baz' => 'bat'];
		/** @var AbstractPart $dummy */
		$dummy = $this->getMockForAbstractClass('Akeeba\Replace\Engine\AbstractPart', [
			$timer, $params,
		]);

		$newParams = ['this' => 'that', 'here' => 'there'];
		$dummy->setup($newParams);

		$actualParams = $this->getObjectAttribute($dummy, 'parameters');
		$this->assertSame($newParams, $actualParams, 'The parameters must be replaced by setup()');
	}

	/**
	 * An exception must be raised if we try to run setup() after the object is initialized
	 */
	public function testSetupAfterInitialization()
	{
		// Fake timer for testing
		$timer = $this->createMock('Akeeba\Replace\Timer\Timer');
		// Fake parameters
		$params = ['foo' => 'bar', 'baz' => 'bat'];
		/** @var AbstractPart $dummy */
		$dummy = $this->getMockForAbstractClass('Akeeba\Replace\Engine\AbstractPart', [
			$timer, $params,
		]);

		$refObj = new \ReflectionObject($dummy);
		$refProp = $refObj->getProperty('state');
		$refProp->setAccessible(true);
		$refProp->setValue($dummy, PartInterface::STATE_PREPARED);

		$this->expectException('LogicException');
		$this->expectExceptionMessage("Cannot run setup() on an object that is already prepared");

		$newParams = ['this' => 'that', 'here' => 'there'];
		$dummy->setup($newParams);
	}

	public function testPropagateFromObjectInvalid()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	public function testPropagateFromObjectErrors()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	public function testPropagateFromObjectWarnings()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	public function testPropagateFromObjectSteps()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	public function testPropagateFromObjectEverything()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	public function testPropagateToObjectInvalid()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	public function testPropagateToObjectErrors()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	public function testPropagateToObjectWarnings()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	public function testPropagateToObjectSteps()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	public function testPropagateToObjectEverything()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	public function testGetState()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	public function testGetStatus()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}

	public function testTick()
	{
		// TODO Implement me
		$this->markTestIncomplete(sprintf('TODO: Implement %s test', __METHOD__));
	}
}
