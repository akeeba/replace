<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Engine\Core\Action\Database;


use Akeeba\Replace\Database\Metadata\Database;
use Akeeba\Replace\Engine\Core\Response\SQL;

class Collation extends AbstractAction
{
	public function processDatabase(Database $db)
	{
		$newCollation     = $this->getConfig()->getDatabaseCollation();
		$currentCollation = $db->getCollation();

		// Nothing to do?
		if (empty($newCollation) || ($currentCollation == $newCollation))
		{
			return new SQL([], []);
		}

		$collationParts = explode('_', $newCollation);
		$newCharset     = $collationParts[0];
		$driver         = $this->getDbo();
		$queryTemplate  = 'ALTER DATABASE %s CHARACTER SET %s COLLATE %s';
		$backupQuery    = sprintf($queryTemplate, $driver->qn($db->getName()), $db->getCharacterSet(), $db->getCollation());
		$actionQuery    = sprintf($queryTemplate, $driver->qn($db->getName()), $newCharset, $newCollation);

		return new SQL([$actionQuery], [$backupQuery]);
	}

}