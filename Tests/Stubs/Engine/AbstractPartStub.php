<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Stubs\Engine;


use Akeeba\Replace\Engine\AbstractPart;

class AbstractPartStub extends AbstractPart
{
	public $prepareThing = false;
	public $afterPrepareThing = false;
	public $finalizeThing = false;
	public $processCalls = 0;

	public function prepare()
	{
		$this->prepareThing = true;
	}

	public function afterPrepare()
	{
		$this->afterPrepareThing = true;
	}

	public function process()
	{
		$this->processCalls++;

		return $this->processCalls !== 2;
	}

	public function finalize()
	{
		$this->finalizeThing = true;
	}
}