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
use Akeeba\Replace\Engine\ErrorHandling\ErrorAware;
use Akeeba\Replace\Engine\ErrorHandling\ErrorException;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAware;
use Akeeba\Replace\Engine\PartInterface;
use Akeeba\Replace\Engine\StepAware;
use Akeeba\Replace\Tests\Engine\ErrorHandling\ErrorAwareTest;
use Akeeba\Replace\Tests\Stubs\Engine\AbstractPartStub;
use Akeeba\Replace\Tests\Stubs\Timer\TimerAlwaysRunning;

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
		$dummy = $this->makeDummyObject();

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
		$dummy = $this->makeDummyObject();

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
		$dummy = $this->makeDummyObject();

		$invalid = (object)array(
			'error' => new ErrorException('Foobar')
		);

		$dummy->propagateFromObject($invalid);

		$this->assertNull($dummy->getError());
	}

	/**
	 * Test error propagation from objects implementing ErrorAwareInterface. For this to work we need to use an abstract
	 * class. For the reasons why, see the referenced test below.
	 *
	 * Note: I cannot use PHPUnit's mock builder with a concrete object implementing an interface because the mock does
	 *       NOT implement the interface. As a result, propagateFromObject never executes the expected code and
	 *       rightfully so (since we check if the other object implements an interface). I also cannot use a mock of an
	 *       abstract class because in this case PHPUnit cannot find the public inheritErrorFrom method I am trying to
	 *       mock. Therefore I have to write a smelly test which tests the result of a foreign class' execution rather
	 *       than my own code :(
	 *
	 * @see ErrorAwareTest::testInheritErrorFrom()
	 */
	public function testPropagateFromObjectErrors()
	{
		$dummy = $this->makeDummyObject();

		/** @var ErrorAware $errorAware */
		$errorAware = $this->getMockForAbstractClass('\\Akeeba\\Replace\\Tests\\Stubs\\Engine\\ErrorHandling\\ErrorAwareTraitDummy');
		$error = $errorAware->setErrorMessage('Foo bar baz');

		$dummy->propagateFromObject($errorAware);

		$this->assertSame($error, $this->getObjectAttribute($dummy, 'error'), 'propagateFromObject must pull in the error from the other object');
		$this->assertNull($errorAware->getError(), 'propagateFromObject must clear errors on the other object');
	}

	/**
	 * Test warnings propagation from objects implementing WarningsAwareInterface. Kinda smelly. See the referenced test
	 * below.
	 *
	 * @see self::testPropagateFromObjectErrors
	 */
	public function testPropagateFromObjectWarnings()
	{
		$dummy = $this->makeDummyObject();

		/** @var WarningsAware $warningsAware */
		$warningsAware = $this->getMockForAbstractClass('\\Akeeba\\Replace\\Tests\\Stubs\\Engine\\ErrorHandling\\WarningsAwareTraitDummy');
		$warnings = $warningsAware->getWarnings();

		$mustNotExist = $dummy->addWarningMessage('This warning is removed');
		$dummy->propagateFromObject($warningsAware);

		$actualWarnings = $dummy->getWarnings();

		$this->assertNotContains($mustNotExist, $actualWarnings, 'propagateFromObject must replace our warnings');
		$this->assertEquals($warnings, $actualWarnings, 'propagateFromObject must replace our warnings with the other object\'s warnings');
	}

	/**
	 * Test step / substep propagation from objects implementing StepAwareInterface. Kinda smelly. See the referenced
	 * test below.
	 *
	 * @see self::testPropagateFromObjectErrors
	 */
	public function testPropagateFromObjectSteps()
	{
		$dummy = $this->makeDummyObject();

		/** @var StepAware $stepAware */
		$stepAware = $this->getMockForAbstractClass('\\Akeeba\\Replace\\Tests\\Stubs\\Engine\\StepAwareTraitDummy');
		$stepAware->setStep('Foo');
		$stepAware->setSubstep('Bar');

		$dummy->setStep('Baz');
		$dummy->setSubstep('Bat');
		$dummy->propagateFromObject($stepAware);

		/**
		 * Heads up! Unlike errors and warnings, propagating steps and substeps MUST NOT reset them on the other object.
		 * The idea is that errors and warnings are transient and bubble up to the outermost part where the application
		 * can interact with them (display them, take a decision based on their existence, ...) whereas step and substep
		 * may in fact be used for internal logging by the Engine Part after propagation, e.g. on __destruct() or
		 * __sleep().
		 */

		$this->assertEquals('Foo', $dummy->getStep(), 'propagateFromObject must pull the step from the other object');
		$this->assertNotEmpty($stepAware->getStep(), 'propagateFromObject must NOT reset the step on the other object');

		$this->assertEquals('Bar', $dummy->getSubstep(), 'propagateFromObject must pull the substep from the other object');
		$this->assertNotEmpty($stepAware->getSubstep(), 'propagateFromObject must NOT reset the substep on the other object');
	}

	public function testPropagateToObjectInvalid()
	{
		$dummy = $this->makeDummyObjectWithExtras();

		$invalid = (object) array(
			'error'    => null,
			'warnings' => [],
			'step'     => '',
			'substep'  => '',
		);

		$dummy->propagateToObject($invalid);

		$this->assertNotNull($dummy->getError());
		$this->assertNotEmpty($dummy->getWarnings());
	}

	public function testPropagateToObjectErrors()
	{
		$dummy = $this->makeDummyObjectWithExtras();

		/** @var ErrorAware $errorAware */
		$errorAware = $this->getMockForAbstractClass('\\Akeeba\\Replace\\Tests\\Stubs\\Engine\\ErrorHandling\\ErrorAwareTraitDummy');

		$error = $dummy->getError();
		$dummy->propagateToObject($errorAware);

		$this->assertSame($error, $this->getObjectAttribute($errorAware, 'error'), 'propagateToObject must push the error to the other object');
		$this->assertNull($dummy->getError(), 'propagateToObject must clear errors on ourselves');
	}

	public function testPropagateToObjectWarnings()
	{
		$dummy = $this->makeDummyObjectWithExtras();

		/** @var WarningsAware $warningsAware */
		$warningsAware = $this->getMockForAbstractClass('\\Akeeba\\Replace\\Tests\\Stubs\\Engine\\ErrorHandling\\WarningsAwareTraitDummy');

		$warnings = $dummy->getWarnings();
		$mustNotExist = $warningsAware->addWarningMessage('This warning is removed');
		$dummy->propagateToObject($warningsAware);

		$actualWarnings = $warningsAware->getWarnings();

		$this->assertNotContains($mustNotExist, $actualWarnings, 'propagateToObject must replace the other object\'s warnings');
		$this->assertEquals($warnings, $actualWarnings, 'propagateToObject must replace the other object\'s warnings with ours');
	}

	public function testPropagateToObjectSteps()
	{
		$dummy = $this->makeDummyObjectWithExtras();

		/** @var StepAware $stepAware */
		$stepAware = $this->getMockForAbstractClass('\\Akeeba\\Replace\\Tests\\Stubs\\Engine\\StepAwareTraitDummy');

		$stepAware->setStep('Baz');
		$stepAware->setSubstep('Bat');

		$dummy->setStep('Foo');
		$dummy->setSubstep('Bar');

		$dummy->propagateToObject($stepAware);

		/**
		 * Heads up! Unlike errors and warnings, propagating steps and substeps MUST NOT reset them on the other object.
		 */

		$this->assertEquals('Foo', $stepAware->getStep(), 'propagateToObject must push the step to the other object');
		$this->assertNotEmpty($dummy->getStep(), 'propagateToObject must NOT reset the step on ourselves');

		$this->assertEquals('Bar', $stepAware->getSubstep(), 'propagateToObject must psuh the substep to the other object');
		$this->assertNotEmpty($dummy->getSubstep(), 'propagateToObject must NOT reset the substep on ourselves');
	}

	public function testTick()
	{
		// Fake timer for testing - always tells us we have time left
		$timerProphet = $this->prophesize('Akeeba\Replace\Timer\Timer')
		                     ->willImplement('Akeeba\Replace\Timer\TimerInterface')
		;
		$timerProphet->getTimeLeft()->willReturn(1);
		$timer = $timerProphet->reveal();

		// Fake parameters, no used
		$params = ['foo' => 'bar', 'baz' => 'bat'];

		/** @var AbstractPart $dummy Object under test */
		$dummy = new AbstractPartStub($timer, $params);

		// Our expectations each time we run tick. The number corresponds to the run number reported by assertions.
		$expectations = [
			0 => ['state' => PartInterface::STATE_INIT, 'prepareThing' => false],
			1 => ['state' => PartInterface::STATE_PREPARED, 'prepareThing' => true],
			2 => ['state' => PartInterface::STATE_RUNNING, 'afterPrepareThing' => true],
			3 => ['state' => PartInterface::STATE_RUNNING, 'processCalls' => 1],
			4 => ['state' => PartInterface::STATE_POSTRUN, 'processCalls' => 2],
			5 => ['state' => PartInterface::STATE_FINALIZED, 'finalizeThing' => true],
		];

		$currentExpectation = [];
		$run                = 0;
		$status             = $dummy->getStatus();

		// Loop until our object finishes running
		do
		{
			$currentExpectation = array_merge($currentExpectation, array_shift($expectations));

			foreach ($currentExpectation as $prop => $expected)
			{
				$this->assertEquals($expected, $this->getObjectAttribute($dummy, $prop), sprintf("Run %d: Property %s does not match expected value", $run, $prop));
			}

			if ($status->isDone())
			{
				break;
			}

			$run++;
			$status = $dummy->tick();
			$error  = $status->getError();

			if (!is_null($error))
			{
				throw $error;
			}
		} while (true);

		$this->assertEmpty($expectations, "tick() ran fewer times than expected!");
	}

	/**
	 * @return AbstractPart
	 */
	private function makeDummyObject()
	{
		// Fake timer for testing
		$timer = $this->createMock('Akeeba\Replace\Timer\Timer');
		// Fake parameters
		$params = ['foo' => 'bar', 'baz' => 'bat'];
		/** @var AbstractPart $dummy */
		$dummy = $this->getMockForAbstractClass('Akeeba\Replace\Engine\AbstractPart', [
			$timer,
			$params,
		]);

		return $dummy;
	}

	/**
	 * Create an AbstractPart object with an error, three warnings, a step and a substep set up. This is used to test
	 * the propagateToObject method.
	 *
	 * @return AbstractPart
	 */
	private function makeDummyObjectWithExtras()
	{
		$dummy = $this->makeDummyObject();

		$dummy->setErrorMessage('Foo bar baz');

		$dummy->addWarningMessage('Foo');
		$dummy->addWarningMessage('Bar');
		$dummy->addWarningMessage('Baz');

		$dummy->setStep('Foo');
		$dummy->setSubstep('Bar');

		return $dummy;
	}
}
