<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

// Include the library autoloader
if (false == include __DIR__ . '/../../../vendor/autoload.php')
{
	echo 'ERROR: The Composer autoloader was not found' . "\n";

	exit(1);
}

if (false == include __DIR__ . '/../../../vendor/fzaninotto/faker/src/autoload.php')
{
	echo 'ERROR: The Faker autoloader was not found' . "\n";

	exit(1);
}

$faker = Faker\Factory::create();

$fp = fopen('large_table.xml', 'wt');

fputs($fp, "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n");
fputs($fp, "<table name=\"tst_large\">\n");
fputs($fp, "\t<column>id</column>\n");
fputs($fp, "\t<column>name</column>\n");
fputs($fp, "\t<column>something</column>\n");

for ($i = 1; $i <= 1234; $i++)
{
	$name = $faker->name;
	$text = $faker->text;
	$content = <<< XML
	<row>
		<value>$i</value>
		<value>$name</value>
		<value>$text</value>
	</row>

XML;
	fputs($fp, $content);
}

fputs($fp, "</table>\n\n");
fclose($fp);