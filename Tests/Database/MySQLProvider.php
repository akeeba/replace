<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Database;


class MySQLProvider
{
	public static function testEscapeProvider()
	{
		return [
			["'%_abc123", false, '\\\'%_abc123'],
			["'%_abc123", true, '\\\'\\%\_abc123'],
			["foo", false, "foo"],
			["Ελληνικά", false, "Ελληνικά"],
		];
	}

	public function testTransactionRollbackProvider()
	{
		return array(
			// $toSavepoint, $tupleCount
			array(null, 0),
			array('transactionSavepoint', 1)
		);
	}

}