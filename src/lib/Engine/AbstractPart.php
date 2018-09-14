<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine;

use Akeeba\Replace\Engine\ErrorHandling\ErrorAware;
use Akeeba\Replace\Engine\ErrorHandling\ErrorAwareInterface;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAware;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAwareInterface;
use Akeeba\Replace\Timer\TimerAware;
use Akeeba\Replace\Timer\TimerAwareInterface;
use Akeeba\Replace\Timer\TimerInterface;

/**
 * An abstract class which implements the PartInterface
 *
 * @package Akeeba\Replace\Engine
 */
abstract class AbstractPart implements PartInterface, TimerAwareInterface, ErrorAwareInterface, WarningsAwareInterface, StepAwareInterface, DomainAwareInterface
{
	use TimerAware;
	use ErrorAware;
	use WarningsAware;
	use StepAware;
	use DomainAware;

	/**
	 * The current state of the engine part
	 *
	 * @var  int
	 */
	protected $state = PartInterface::STATE_INIT;

	/**
	 * The configuration parameters for this engine part
	 *
	 * @var  array
	 */
	protected $parameters = array();

	/**
	 * AbstractPart constructor.
	 *
	 * @param   TimerInterface  $timer   The timer object used by this part
	 * @param   array           $params  Configuration parameters as a keyed array
	 */
	public function __construct(TimerInterface $timer, array $params)
	{
		$this->setTimer($timer);

		$this->state = PartInterface::STATE_INIT;
		$this->setup($params);
	}

	/**
	 * Pass configuration parameters to the Engine Part. You need to do this before the first call to tick().
	 *
	 * @param   array  $parametersArray  The configuration parameters
	 *
	 * @return  void
	 */
	public final function setup(array $parametersArray)
	{
		if ($this->state >= PartInterface::STATE_PREPARED)
		{
			throw new \LogicException("Cannot run setup() on an object that is already prepared");
		}

		$this->parameters = $parametersArray;
	}

	/**
	 * Process one or more steps, until the timer tells us that we are running out of time.
	 *
	 * This method calls one of _prepare(), _run() and _finalize() depending on the internal state. If the state is
	 * STATE_FINALIZED no further action will be taken, just the status object returned.
	 *
	 * @return  PartStatus
	 */
	public final function tick()
	{
		switch ($this->state)
		{
			case PartInterface::STATE_INIT:
				$this->prepare();
				break;

			case PartInterface::STATE_PREPARED:
			case PartInterface::STATE_RUNNING:
				$this->mainProcessing();
				break;

			case PartInterface::STATE_POSTRUN:
				$this->finalize();
				break;
		}

		return $this->getStatus();
	}

	/**
	 * Bump the internal state to the next one. The state progression is Init, Prepared, Running, Post-run, Finalized.
	 * Call this from _prepare(), _run() and _finalize() as necessary
	 *
	 * @return  void
	 */
	protected final function nextState()
	{
		switch ($this->state)
		{
			case PartInterface::STATE_INIT:
				$this->state = PartInterface::STATE_PREPARED;
				break;

			case PartInterface::STATE_PREPARED:
				$this->state = PartInterface::STATE_RUNNING;
				break;

			case PartInterface::STATE_RUNNING:
				$this->state = PartInterface::STATE_POSTRUN;
				break;

			case PartInterface::STATE_POSTRUN:
				$this->state = PartInterface::STATE_FINALIZED;
				break;
		}
	}

	/**
	 * Executes when the state is STATE_INIT. You are supposed to set up internal objects and do any other kind of
	 * preparatory work which does not take too much time; this is executed outside of a timer context (unless you
	 * implement such logic yourself). After you're done call nextState() to set the internal state to STATE_PREPARED.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	protected function prepare()
	{
		$this->nextState();
	}

	/**
	 * Main processing. Calls _afterPrepare() exactly once and process() at least once.
	 *
	 * @return  void
	 */
	private final function mainProcessing()
	{
		// Is this the first tick of a running state? Run afterPrepare().
		if ($this->state == PartInterface::STATE_PREPARED)
		{
			$this->afterPrepare();
			$this->nextState();

			return;
		}

		if ($this->process() === false)
		{
			$this->nextState();
		}
	}

	/**
	 * Executes exactly once, at the first step of the run loop. Use it to perform any kind of set up that takes a
	 * non-trivial amount of time. This is optional and can be left blank.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	protected function afterPrepare()
	{
	}

	/**
	 * Main processing. Here you do the bulk of the work. When you no longer have any more work to do return boolean
	 * false.
	 *
	 * @return  bool
	 *
	 * @codeCoverageIgnore
	 */
	protected function process()
	{
		return false;
	}

	/**
	 * Finalization. Here you are supposed to perform any kind of tear down after your work is done. Remember to call
	 * nextState afterwards.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 */
	protected function finalize()
	{
		$this->nextState();
	}

	/**
	 * Returns the status object for this Engine Part.
	 *
	 * @return  PartStatus
	 *
	 * @codeCoverageIgnore
	 */
	public final function getStatus()
	{
		return PartStatus::fromPart($this);
	}

	/**
	 * Get the Engine Part running state. See the constants defined in the PartInterface.
	 *
	 * @return  int
	 *
	 * @codeCoverageIgnore
	 */
	public final function getState()
	{
		return $this->state;
	}

	/**
	 * Propagate errors and warnings from an object, if the object supports the ErrorAwareInterface and / or
	 * WarningsAwareInterface. Also propagates the step and substep if the object supports StepAwareInterface.
	 *
	 * @param   object  $object  The object to propagate from
	 *
	 * @return  void
	 */
	public final function propagateFromObject($object)
	{
		if ($object instanceof ErrorAwareInterface)
		{
			$this->inheritErrorFrom($object);
		}

		if ($object instanceof WarningsAwareInterface)
		{
			$this->inheritWarningsFrom($object);
		}

		if ($object instanceof StepAwareInterface)
		{
			$this->setStep($object->getStep());
			$this->setSubstep($object->getSubstep());
		}
	}

	/**
	 * Symmetrical function to propagateFromObject.
	 *
	 * @param   object  $object  The object to propagate to
	 *
	 * @return  void
	 */
	public final function propagateToObject($object)
	{
		if ($object instanceof ErrorAwareInterface)
		{
			$object->inheritErrorFrom($this);
		}

		if ($object instanceof WarningsAwareInterface)
		{
			$object->inheritWarningsFrom($this);
		}

		if ($object instanceof StepAwareInterface)
		{
			$object->setStep($this->getStep());
			$object->setSubstep($this->getSubstep());
		}
	}
}