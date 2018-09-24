<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Stubs\Core\Action\Database;


use Akeeba\Replace\Engine\Core\Action\ActionAware as GenericActionAware;
use Akeeba\Replace\Engine\Core\Action\ActionAwareInterface;
use Akeeba\Replace\Engine\Core\Action\Database\ActionAware;

class ActionAwareDummyNoWarnings implements ActionAwareInterface
{
	use ActionAware;
	use GenericActionAware;
}