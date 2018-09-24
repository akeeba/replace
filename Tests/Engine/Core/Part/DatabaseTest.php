<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Engine\Core\Part;

use Akeeba\Replace\Engine\Core\Part\Database;

/**
 * Since we are testing an engine part this is really an integration test, not a unit test. We have already tested the
 * "meat" of the engine through the unit tests. This is just making sure that when everything is thrown together it
 * _still_ works as intended.
 *
 * @package Akeeba\Replace\Tests\Engine\Core\Part
 */
class DatabaseTest extends \PHPUnit_Extensions_Database_TestCase
{
	/**
	 * Runs before any tests from this class execute.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		// Get the schema filename based on the driver's database technology
		$schemaFilename = AKEEBA_TEST_ROOT . '/_data/schema/engine_parts_test.sql';

		// Make sure the database tables exist
		$pdo = new \PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8", $_ENV['DB_USER'], $_ENV['DB_PASS']);
		$pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 0);
		$queries = file_get_contents($schemaFilename);
		$pdo->exec($queries);
	}

	/**
	 * Returns the default database connection for running the tests. This is the internal connection used by PHPUnit
	 * to do thing, like apply our data set. It is not used by the driver object being tested.
	 *
	 * @return  \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
	 */
	protected function getConnection()
	{
		$pdo = new \PDO("mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8", $_ENV['DB_USER'], $_ENV['DB_PASS']);

		return $this->createDefaultDBConnection($pdo, $_ENV['DB_NAME']);
	}

	/**
	 * Gets the data set to be loaded into the database during setup. This is applied to the database by PHPUnit.
	 *
	 * @return  \PHPUnit_Extensions_Database_DataSet_IDataSet
	 */
	protected function getDataSet()
	{
		return new \PHPUnit_Extensions_Database_DataSet_XmlDataSet(AKEEBA_TEST_ROOT . '/_data/schema/engine_parts_test.xml');
	}

	// TODO Implement the test :)
}
