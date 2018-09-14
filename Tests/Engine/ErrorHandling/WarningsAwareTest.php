<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Engine\ErrorHandling;

use Akeeba\Replace\Engine\ErrorHandling\WarningException;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAware;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAwareInterface;

class WarningsAwareTest extends \PHPUnit_Framework_TestCase
{
	public function testAddWarning()
	{
		/** @var WarningsAware $dummyObject */
		$dummyObject = $this->getObjectForTrait('Akeeba\Replace\Engine\ErrorHandling\WarningsAware');

		$this->assertEquals(array(), $this->getObjectAttribute($dummyObject, 'warnings'), 'No warnings must be present initially');

		$warnings = [
			new WarningException('Foo')
		];
		$dummyObject->addWarning($warnings[0]);
		$this->assertEquals($warnings, $this->getObjectAttribute($dummyObject, 'warnings'), 'Adding first warning');

		$warnings[] = new WarningException('Bar');
		$dummyObject->addWarning($warnings[1]);
		$this->assertEquals($warnings, $this->getObjectAttribute($dummyObject, 'warnings'), 'Adding second warning');

		$warnings[] = new WarningException('Baz');
		$dummyObject->addWarning($warnings[2]);
		$this->assertEquals($warnings, $this->getObjectAttribute($dummyObject, 'warnings'), 'Adding third warning');
	}

	public function testSetWarningsQueueLengthZero()
	{
		/**
		 * Get a dummy object with twelve warnings
		 *
		 * @var WarningsAware $dummyObject
		 * @var WarningException[] $manyWarnings
		 */
		list($dummyObject, $manyWarnings) = $this->makeDummyObjectWithTwelveWarnings();

		$dummyObject->setWarningsQueueLength(0);
		$this->assertCount(12, $this->getObjectAttribute($dummyObject, 'warnings'), 'Setting the queue length to zero must allow for all twelve objects already present');

		$dummyObject->addWarning(new WarningException('Lucky thirteen'));
		$this->assertCount(13, $this->getObjectAttribute($dummyObject, 'warnings'), 'Adding a warning to an object with zero length queue adds it without complaining (i.e. there is no queue length control).');
	}

	public function testSetWarningsQueueLengthWithEnoughSpace()
	{
		/**
		 * Get a dummy object with twelve warnings
		 *
		 * @var WarningsAware $dummyObject
		 * @var WarningException[] $manyWarnings
		 */
		list($dummyObject, $manyWarnings) = $this->makeDummyObjectWithTwelveWarnings();

		$dummyObject->setWarningsQueueLength(13);
		$this->assertCount(12, $this->getObjectAttribute($dummyObject, 'warnings'), 'Setting the queue length to 13 must allow for all twelve objects already present');

		$thirteen = new WarningException('Lucky thirteen');
		$manyWarnings[] = $thirteen;
		$dummyObject->addWarning($thirteen);
		$actual = $this->getObjectAttribute($dummyObject, 'warnings');
		$this->assertCount(13, $actual, 'Adding thirteenth warning to an object with queue length 13 must succeed');
		$this->assertEquals($manyWarnings, $actual, 'The first 13 warnings must match up');
	}

	public function testSetWarningsQueueLengthWithTruncate()
	{
		/**
		 * Get a dummy object with twelve warnings
		 *
		 * @var WarningsAware $dummyObject
		 * @var WarningException[] $manyWarnings
		 */
		list($dummyObject, $manyWarnings) = $this->makeDummyObjectWithTwelveWarnings();

		$expected = [array_pop($manyWarnings)];

		$dummyObject->setWarningsQueueLength(1);
		$actual = $this->getObjectAttribute($dummyObject, 'warnings');
		$this->assertCount(1, $actual, 'Setting the queue length to 1 must remove all elements except the last one');
		$this->assertEquals($expected, $actual, 'The leftover element after queue resize to length 1 must be the last one in the queue');
	}

	public function testAddWarningWithOverflow()
	{
		/**
		 * Get a dummy object with twelve warnings
		 *
		 * @var WarningsAware $dummyObject
		 * @var WarningException[] $manyWarnings
		 */
		list($dummyObject, $manyWarnings) = $this->makeDummyObjectWithTwelveWarnings();

		$dummyObject->setWarningsQueueLength(12);

		$thirteen = new WarningException('Lucky thirteen');
		$manyWarnings[] = $thirteen;
		array_shift($manyWarnings);
		$dummyObject->addWarning($thirteen);
		$actual = $this->getObjectAttribute($dummyObject, 'warnings');
		$this->assertCount(12, $actual, 'Adding thirteenth warning to an object with queue length 12 must result in 12 items being present afterwards');
		$this->assertEquals($manyWarnings, $actual, 'Adding 13th warning to a queue with length 12 must bump the first element off the queue');
	}

	public function testGetWarnings()
	{
		/**
		 * Get a dummy object with twelve warnings
		 *
		 * @var WarningsAware $dummyObject
		 * @var WarningException[] $manyWarnings
		 */
		list($dummyObject, $manyWarnings) = $this->makeDummyObjectWithTwelveWarnings();

		$actual = $dummyObject->getWarnings();

		$this->assertEquals($manyWarnings, $actual, 'getWarnings must return the correct queue contents');
	}

	public function testAddWarningMessageWithObject()
	{
		/** @var WarningsAware $dummyObject */
		$dummyObject = $this->getObjectForTrait('\\Akeeba\\Replace\\Engine\\ErrorHandling\\WarningsAware');
		$errorObject = new WarningException('Foo bar baz');

		$this->expectException('\\InvalidArgumentException');
		$this->expectExceptionMessage('Parameter $message to ');
		$dummyObject->addWarningMessage($errorObject);
	}

	public function testAddWarningMessageWithString()
	{
		/** @var WarningsAware $dummyObject */
		$dummyObject = $this->getObjectForTrait('Akeeba\Replace\Engine\ErrorHandling\WarningsAware');

		$this->assertEmpty($this->getObjectAttribute($dummyObject, 'warnings'), 'Initial queu must be empty');

		$message = 'Foo bar';
		$actual  = $dummyObject->addWarningMessage($message);

		$this->assertInternalType('object', $actual, 'addWarningMessage must return an object when a non-empty string is passed');
		$this->assertEquals('Akeeba\Replace\Engine\ErrorHandling\WarningException', get_class($actual), 'setErrorMessage must return a WarningException object when a non-empty string is passed');
		$this->assertEquals($message, $actual->getMessage(), 'addWarningMessage must return a WarningException object with the correct message');
		$this->assertEquals(0, $actual->getCode(), 'addWarningMessage must return a WarningException object with the correct code (zero)');
		$internal = $this->getObjectAttribute($dummyObject, 'warnings');
		$this->assertSame($actual, $internal[0], "The returned object must be a reference to the internal object");

	}

	public function testResetWarnings()
	{
		/**
		 * Get a dummy object with twelve warnings
		 *
		 * @var WarningsAware $dummyObject
		 * @var WarningException[] $manyWarnings
		 */
		list($dummyObject, $manyWarnings) = $this->makeDummyObjectWithTwelveWarnings();

		$this->assertCount(12, $dummyObject->getWarnings());

		$dummyObject->resetWarnings();

		$this->assertCount(0, $dummyObject->getWarnings());
	}

	public function testInheritWarningsFrom()
	{
		/**
		 * IMPORTANT! The $parentObject needs to implement the WarningsAwareInterface. This is only possible by using a
		 *            dummy abstract class and getMockForAbstractClass(). That's because getObjectForTrait() will return
		 *            an object which does not implement any interface. Moreover, traits cannot be implementing
		 *            interfaces (as of PHP 7.2) even if they actually contain all the methods to do so. That's a PHP
		 *            shortcoming.
		 */

		/** @var WarningsAware|WarningsAwareInterface $parentObject */
		$parentObject = $this->getMockForAbstractClass('Akeeba\Replace\Tests\Stubs\Engine\ErrorHandling\WarningsAwareTraitDummy');

		/** @var WarningsAware $childObject */
		$childObject = $this->getObjectForTrait('\\Akeeba\\Replace\\Engine\\ErrorHandling\\WarningsAware');

		// First, assign twelve elements to the queue bypassing all controls (because I'm nasty like that)
		$manyWarnings = $this->makeSomeWarnings();
		$refObject    = new \ReflectionObject($parentObject);
		$refProp      = $refObject->getProperty('warnings');
		$refProp->setAccessible(true);
		$refProp->setValue($parentObject, $manyWarnings);

		if ($childObject === $parentObject)
		{
			// Should never happen: same object returned by PHPUnit. This makes our test inconclusive.
			$this->markAsRisky();
		}

		$actual = $childObject->getWarnings();
		$this->assertEmpty($actual, "No warning must be set in the child object before we inherit from our parent.");

		$childObject->inheritWarningsFrom($parentObject);

		$actual = $childObject->getWarnings();
		$this->assertSame($manyWarnings, $actual, "Inheriting warnings must set a reference to the original warnigns");

		$actual = $parentObject->getWarnings();
		$this->assertEmpty($actual, "After inheriting from the parent, the parent's warnings must be cleared.");

	}

	private function makeSomeWarnings()
	{
		return [
			new WarningException('Foo'),
			new WarningException('Bar'),
			new WarningException('Baz'),
			new WarningException('Bat'),
			new WarningException('Foo Bar'),
			new WarningException('Foo Baz'),
			new WarningException('Foo Bat'),
			new WarningException('Bar Baz'),
			new WarningException('Bar Bat'),
			new WarningException('Foo Bar Baz'),
			new WarningException('Foo Bar Bat'),
			new WarningException('Foo Bar Baz Bat'),
		];
	}

	/**
	 * @return  array
	 */
	private function makeDummyObjectWithTwelveWarnings()
	{
		/** @var WarningsAware $dummyObject */
		$dummyObject = $this->getObjectForTrait('Akeeba\Replace\Engine\ErrorHandling\WarningsAware');

		// First, assign twelve elements to the queue bypassing all controls (because I'm nasty like that)
		$manyWarnings = $this->makeSomeWarnings();
		$refObject    = new \ReflectionObject($dummyObject);
		$refProp      = $refObject->getProperty('warnings');
		$refProp->setAccessible(true);
		$refProp->setValue($dummyObject, $manyWarnings);

		return [$dummyObject, $manyWarnings];
	}
}
