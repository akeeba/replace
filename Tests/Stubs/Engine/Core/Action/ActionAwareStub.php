<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Stubs\Engine\Core\Action;


use Akeeba\Replace\Engine\Core\Action\ActionAware;
use Akeeba\Replace\Engine\Core\Action\ActionAwareInterface;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAware;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAwareInterface;
use Akeeba\Replace\Logger\LoggerAware;
use Akeeba\Replace\Logger\LoggerAwareInterface;
use Akeeba\Replace\Logger\NullLogger;

class ActionAwareStub implements ActionAwareInterface, WarningsAwareInterface, LoggerAwareInterface
{
	use ActionAware;
	use WarningsAware;
	use LoggerAware;

	/**
	 * ActionAwareStub constructor.
	 */
	public function __construct()
	{
		$logger = new NullLogger();

		$this->setLogger($logger);
	}
}