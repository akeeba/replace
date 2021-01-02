<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

class SomeRandomClass
{
	private $foo;

	public function __construct($foo)
	{
		$this->foo = $foo;
	}
}

$data = [
	'Just BORGing',
	'Not borged',
	'The borg',
	'The bar is open',
	'Hello barman',
	'Forg',
	'Zorg',
];

foreach ($data as $string)
{
	$key1 = "stdClass with “{$string}”";
	$key2 = "SomeRandomClass with “{$string}”";
	$key3 = "array with “{$string}”";

	$array         = [
		'foo'   => '',
		'bar'   => $string,
		'bat'   => 'dorg',
		$string => 'morg'
	];
	$serialized1 = serialize((object) $array);
	$serialized2 = serialize(new SomeRandomClass($string));
	$serialized3 = serialize($array);

	echo <<< XML
<row>
	<value>$key1</value>
	<value>$serialized1</value>
</row>
<row>
	<value>$key2</value>
	<value>$serialized2</value>
</row>
<row>
	<value>$key3</value>
	<value>$serialized3</value>
</row>

XML;

}