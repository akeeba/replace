<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Stubs\Core\Action\Database;


use Akeeba\Replace\Database\Metadata\Database;
use Akeeba\Replace\Engine\Core\Action\Database\AbstractAction;
use Akeeba\Replace\Engine\Core\Response\SQL;

class FakeAction extends AbstractAction
{
	public function processDatabase(Database $db)
	{
		return new SQL(['Foo'], ['Bar']);
	}

}