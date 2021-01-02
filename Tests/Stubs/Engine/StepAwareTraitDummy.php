<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Stubs\Engine;

use Akeeba\Replace\Engine\StepAware;
use Akeeba\Replace\Engine\StepAwareInterface;

/**
 * Dummy object to test the ErrorAware trait.
 *
 * We need an abstract class because PHPUnit's getObjectForTrait() does not let us indicate that the generated mock
 * object implements a specific interface. Therefore we need to create an abstract class and use getMockForAbstractClass
 * to achieve our intended result.
 *
 * @see https://stackoverflow.com/questions/12891606/mock-interface-and-trait-simultaneously
 *
 * @package Akeeba\Replace\Tests\Stubs\Engine\ErrorHandling
 */
abstract class StepAwareTraitDummy implements StepAwareInterface
{
	use StepAware;
}