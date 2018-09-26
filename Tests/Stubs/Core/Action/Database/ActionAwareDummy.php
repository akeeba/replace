<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Stubs\Core\Action\Database;


use Akeeba\Replace\Engine\ErrorHandling\WarningsAware;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAwareInterface;

class ActionAwareDummy extends ActionAwareDummyNoWarnings implements WarningsAwareInterface
{
	use WarningsAware;
}