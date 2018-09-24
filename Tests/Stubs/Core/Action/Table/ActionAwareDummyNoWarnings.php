<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Stubs\Core\Action\Table;


use Akeeba\Replace\Engine\Core\Action\ActionAware as GenericActionAware;
use Akeeba\Replace\Engine\Core\Action\ActionAwareInterface;
use Akeeba\Replace\Engine\Core\Action\Table\ActionAware;

class ActionAwareDummyNoWarnings implements ActionAwareInterface
{
	use ActionAware;
	use GenericActionAware;
}