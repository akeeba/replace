<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Database\Metadata;

use Akeeba\Replace\Database\Metadata\Column;

class ColumnTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @param array  $input
	 * @param string $name
	 * @param string $collation
	 * @param string $keyName
	 * @param string $type
	 * @param bool   $isPK
	 * @param bool   $isText
	 * @param bool   $isAutoIncrement
	 *
	 * @dataProvider fromDatabaseResultProvider()
	 */
	public function testFromDatabaseResult(array $input, $name, $collation, $keyName, $type, $isPK, $isText, $isAutoIncrement)
	{
		$actual = Column::fromDatabaseResult($input);

		$this->assertInstanceOf(Column::class, $actual);
		$this->assertEquals($name, $actual->getColumnName());
		$this->assertEquals($collation, $actual->getCollation());
		$this->assertEquals($keyName, $actual->getKeyName());
		$this->assertEquals($type, $actual->getType());
		$this->assertEquals($isPK, $actual->isPK());
		$this->assertEquals($isText, $actual->isText());
		$this->assertEquals($isAutoIncrement, $actual->isAutoIncrement());
	}

	public static function fromDatabaseResultProvider()
	{
		return [
			'text PK, information_schema' => [
				json_decode('{
				    "TABLE_CATALOG": "def",
				    "TABLE_SCHEMA": "wordpress",
				    "TABLE_NAME": "wp_ak_params",
				    "COLUMN_NAME": "tag",
				    "ORDINAL_POSITION": 1,
				    "COLUMN_DEFAULT": null,
				    "IS_NULLABLE": "NO",
				    "DATA_TYPE": "varchar",
				    "CHARACTER_MAXIMUM_LENGTH": 191,
				    "CHARACTER_OCTET_LENGTH": 764,
				    "NUMERIC_PRECISION": null,
				    "NUMERIC_SCALE": null,
				    "DATETIME_PRECISION": null,
				    "CHARACTER_SET_NAME": "utf8mb4",
				    "COLLATION_NAME": "utf8mb4_unicode_ci",
				    "COLUMN_TYPE": "varchar(191)",
				    "COLUMN_KEY": "PRI",
				    "EXTRA": "",
				    "PRIVILEGES": "select,insert,update,references",
				    "COLUMN_COMMENT": "",
				    "GENERATION_EXPRESSION": ""
				  }', true),
				// name, collation, keyname, type
				'tag', 'utf8mb4_unicode_ci', 'PRI', 'varchar(191)',
				// isPK, isText, isAutoIncrement
				true, true, false
			],
			'text PK, show full columns' => [
				json_decode('{
				    "Field": "tag",
				    "Type": "varchar(191)",
				    "Collation": "utf8mb4_unicode_ci",
				    "Null": "NO",
				    "Key": "PRI",
				    "Default": null,
				    "Extra": "",
				    "Privileges": "select,insert,update,references",
				    "Comment": ""
				  }', true),
				// name, collation, keyname, type
				'tag', 'utf8mb4_unicode_ci', 'PRI', 'varchar(191)',
				// isPK, isText, isAutoIncrement
				true, true, false
			],
			'text, non-PK, information_schema' => [
				json_decode('{
				    "TABLE_CATALOG": "def",
				    "TABLE_SCHEMA": "wordpress",
				    "TABLE_NAME": "wp_ak_params",
				    "COLUMN_NAME": "data",
				    "ORDINAL_POSITION": 2,
				    "COLUMN_DEFAULT": null,
				    "IS_NULLABLE": "YES",
				    "DATA_TYPE": "longtext",
				    "CHARACTER_MAXIMUM_LENGTH": 4294967295,
				    "CHARACTER_OCTET_LENGTH": 4294967295,
				    "NUMERIC_PRECISION": null,
				    "NUMERIC_SCALE": null,
				    "DATETIME_PRECISION": null,
				    "CHARACTER_SET_NAME": "utf8mb4",
				    "COLLATION_NAME": "utf8mb4_unicode_ci",
				    "COLUMN_TYPE": "longtext",
				    "COLUMN_KEY": "",
				    "EXTRA": "",
				    "PRIVILEGES": "select,insert,update,references",
				    "COLUMN_COMMENT": "",
				    "GENERATION_EXPRESSION": ""
				  }', true),
				// name, collation, keyname, type
				'data', 'utf8mb4_unicode_ci', '', 'longtext',
				// isPK, isText, isAutoIncrement
				false, true, false
			],
			'text, non-PK, show full columns' => [
				json_decode('{
				    "Field": "data",
				    "Type": "longtext",
				    "Collation": "utf8mb4_unicode_ci",
				    "Null": "YES",
				    "Key": "",
				    "Default": null,
				    "Extra": "",
				    "Privileges": "select,insert,update,references",
				    "Comment": ""
				  }', true),
				// name, collation, keyname, type
				'data', 'utf8mb4_unicode_ci', '', 'longtext',
				// isPK, isText, isAutoIncrement
				false, true, false
			],

			'auto-increment, PK, information_schema' => [
				json_decode('{
				    "TABLE_CATALOG": "def",
				    "TABLE_SCHEMA": "wordpress",
				    "TABLE_NAME": "wp_options",
				    "COLUMN_NAME": "option_id",
				    "ORDINAL_POSITION": 1,
				    "COLUMN_DEFAULT": null,
				    "IS_NULLABLE": "NO",
				    "DATA_TYPE": "bigint",
				    "CHARACTER_MAXIMUM_LENGTH": null,
				    "CHARACTER_OCTET_LENGTH": null,
				    "NUMERIC_PRECISION": 20,
				    "NUMERIC_SCALE": 0,
				    "DATETIME_PRECISION": null,
				    "CHARACTER_SET_NAME": null,
				    "COLLATION_NAME": null,
				    "COLUMN_TYPE": "bigint(20) unsigned",
				    "COLUMN_KEY": "PRI",
				    "EXTRA": "auto_increment",
				    "PRIVILEGES": "select,insert,update,references",
				    "COLUMN_COMMENT": "",
				    "GENERATION_EXPRESSION": ""
				  }', true),
				// name, collation, keyname, type
				'option_id', '', 'PRI', 'bigint(20) unsigned',
				// isPK, isText, isAutoIncrement
				true, false, true
			],
			'auto-increment, PK, show full columns' => [
				json_decode('{
				    "Field": "option_id",
				    "Type": "bigint(20) unsigned",
				    "Collation": null,
				    "Null": "NO",
				    "Key": "PRI",
				    "Default": null,
				    "Extra": "auto_increment",
				    "Privileges": "select,insert,update,references",
				    "Comment": ""
				  }', true),
				// name, collation, keyname, type
				'option_id', '', 'PRI', 'bigint(20) unsigned',
				// isPK, isText, isAutoIncrement
				true, false, true
			],

		];
	}

}
