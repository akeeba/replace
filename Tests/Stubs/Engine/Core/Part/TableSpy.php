<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Tests\Stubs\Engine\Core\Part;

use Akeeba\Replace\Database\Metadata\Table;
use Akeeba\Replace\Engine\ErrorHandling\ErrorAware;
use Akeeba\Replace\Engine\ErrorHandling\ErrorAwareInterface;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAware;
use Akeeba\Replace\Engine\ErrorHandling\WarningsAwareInterface;
use Akeeba\Replace\Engine\PartStatus;
use Akeeba\Replace\Engine\StepAware;
use Akeeba\Replace\Engine\StepAwareInterface;

class TableSpy implements WarningsAwareInterface, ErrorAwareInterface, StepAwareInterface
{
	use ErrorAware, WarningsAware, StepAware;

	public static $instanceParams = [];

	public $meta;

	public function __construct($timer, $db, $logger, $config, $outputWriter, $backupWriter, Table $tableMeta, $memInfo)
	{
		self::$instanceParams[] = $tableMeta;

		$this->meta = $tableMeta;
		$this->setStep($this->meta->getName());
	}

	public function tick()
	{
		$this->setSubstep('1/1');

		return new PartStatus([
			'Done' => 1,
			'Domain' => '',
			'Step' => $this->meta->getName(),
			'Substep' => '1/1',
		]);
	}
}