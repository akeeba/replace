<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Stubs\Core\Action\Table;


use Akeeba\Replace\Database\Metadata\Column;
use Akeeba\Replace\Database\Metadata\Table;
use Akeeba\Replace\Engine\Core\Action\Table\AbstractAction;
use Akeeba\Replace\Engine\Core\Response\SQL;

class FakeAction extends AbstractAction
{
	public function processTable(Table $table, array $columns)
	{
		return new SQL(['Foo'], ['Bar']);
	}
}