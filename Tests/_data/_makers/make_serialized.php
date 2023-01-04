<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

require 'classdefs.php';

use McTestface\SampleOne;
use McTestface\SampleSubclass;

function makeSimpleNamespacedSerialized($withURL, $fileName)
{
	$outerThing               = new SampleSubclass($withURL);
	$innerThing               = new InnerThing();
	$sampleOne                = new SampleOne();
	$sampleOne->publicThingie = new DateTime('2018-01-02 03:04 GMT');

	$innerThing->replaceMe             = rtrim($withURL, '/') . '/' . $innerThing->dontReplaceMe;
	$slashPos                          = strpos($withURL, '/', 8);
	$innerThing->anotherReplacedThing  = substr($withURL, 0, $slashPos);
	$innerThing->sneakyReplacedThingie = substr($withURL, $slashPos) . '/sneaky/thing';

	$innerThing->nestedBit     = (object) [
		'myint'           => 1,
		'stringieThingie' => 'lol not to be replaced',
		'forReplacement'  => $withURL,
		'sample'          => $sampleOne,
	];
	$outerThing->publicThingie = $innerThing;

	file_put_contents('../' . $fileName, serialize($outerThing));
}

function makeSimpleSerialized($withURL, $fileName)
{
	$outerThing               = new SampleTwoSubclass($withURL);
	$innerThing               = new InnerThing();
	$sampleOne                = new SampleTwo();
	$sampleOne->publicThingie = new DateTime('2018-01-02 03:04 GMT');

	$innerThing->replaceMe             = rtrim($withURL, '/') . '/' . $innerThing->dontReplaceMe;
	$slashPos                          = strpos($withURL, '/', 8);
	$innerThing->anotherReplacedThing  = substr($withURL, 0, $slashPos);
	$innerThing->sneakyReplacedThingie = substr($withURL, $slashPos) . '/sneaky/thing';

	$innerThing->nestedBit     = (object) [
		'myint'           => 1,
		'stringieThingie' => 'lol not to be replaced',
		'forReplacement'  => $withURL,
		'sample'          => $sampleOne,
	];
	$outerThing->publicThingie = $innerThing;

	file_put_contents('../' . $fileName, serialize($outerThing));
}

function makeComplexNamespacedSerialized($withURL, $fileName)
{
	$outerThing               = new SampleSubclass($withURL);
	$innerThing               = new InnerThing();
	$sampleOne                = new SampleOne();
	$sampleOne->publicThingie = new DateTime('2018-01-02 03:04 GMT');

	$innerThing->replaceMe             = rtrim($withURL, '/') . '/' . $innerThing->dontReplaceMe;
	$slashPos                          = strpos($withURL, '/', 8);
	$innerThing->anotherReplacedThing  = substr($withURL, 0, $slashPos);
	$innerThing->sneakyReplacedThingie = substr($withURL, $slashPos) . '/sneaky/thing';

	$innerThing->nestedBit     = (object) [
		'myint'           => 1,
		'stringieThingie' => 'lol not to be replaced',
		'forReplacement'  => $withURL,
		'sample'          => $sampleOne,
	];
	$outerThing->publicThingie = $innerThing;

	$thingClone        = clone $outerThing;
	$outerThing->lotsOfFun = [
		$thingClone,
		$thingClone,
		$thingClone,
		$thingClone,
	];

	$deeperInner            = clone $innerThing;
	$deeperInner->nestedBit = (object) [
		'myint'           => 1,
		'stringieThingie' => 'άσε με ήσυχο',
		'forReplacement'  => $withURL,
		'sample'          => clone $outerThing,
	];

	$deeperOuter                = clone $outerThing;
	$deeperOuter->publicThingie = clone $deeperInner;

	for ($i = 0; $i < 20; $i++)
	{
		$deeperOuter->goingDeeper = clone $deeperOuter;
	}

	unset($outerThing);
	$outerThing = clone $deeperOuter;

	$outerThing->nestedExtravaganza = [];

	for ($i = 0; $i < 100; $i++)
	{
		$outerThing->nestedExtravaganza[] = clone $deeperOuter;
	}

	file_put_contents('../' . $fileName, serialize($outerThing));
}

function makeComplexSerialized($withURL, $fileName)
{
	$outerThing               = new SampleTwoSubclass($withURL);
	$innerThing               = new InnerThing();
	$sampleOne                = new SampleTwo();
	$sampleOne->publicThingie = new DateTime('2018-01-02 03:04 GMT');

	$innerThing->replaceMe             = rtrim($withURL, '/') . '/' . $innerThing->dontReplaceMe;
	$slashPos                          = strpos($withURL, '/', 8);
	$innerThing->anotherReplacedThing  = substr($withURL, 0, $slashPos);
	$innerThing->sneakyReplacedThingie = substr($withURL, $slashPos) . '/sneaky/thing';

	$innerThing->nestedBit     = (object) [
		'myint'           => 1,
		'stringieThingie' => 'lol not to be replaced',
		'forReplacement'  => $withURL,
		'sample'          => $sampleOne,
	];
	$outerThing->publicThingie = $innerThing;

	$thingClone        = clone $outerThing;
	$outerThing->lotsOfFun = [
		$thingClone,
		$thingClone,
		$thingClone,
		$thingClone,
	];

	$deeperInner            = clone $innerThing;
	$deeperInner->nestedBit = (object) [
		'myint'           => 1,
		'stringieThingie' => 'άσε με ήσυχο',
		'forReplacement'  => $withURL,
		'sample'          => clone $outerThing,
	];

	$deeperOuter                = clone $outerThing;
	$deeperOuter->publicThingie = clone $deeperInner;

	for ($i = 0; $i < 20; $i++)
	{
		$deeperOuter->goingDeeper = clone $deeperOuter;
	}

	unset($outerThing);
	$outerThing = clone $deeperOuter;

	$outerThing->nestedExtravaganza = [];

	for ($i = 0; $i < 100; $i++)
	{
		$outerThing->nestedExtravaganza[] = clone $deeperOuter;
	}

	file_put_contents('../' . $fileName, serialize($outerThing));
}

function makeSerializedArray($fileName)
{
	$a = [
		'This is a test',
		'Αυτό είναι μια δοκιμή',
		'🐈👌',
		new SampleTwoSubclass('Sample McSampleface')
	];

	$a['nested'] = array_merge($a, []);

	file_put_contents('../' . $fileName, serialize($a));
}

makeSimpleSerialized('http://www.example.com/mysite', 'serialized_simple_ascii.txt');
makeSimpleSerialized('http://www.δοκιμή.com/κάτι', 'serialized_simple_utf8.txt');

makeSimpleNamespacedSerialized('http://www.example.com/mysite', 'serialized_namespace_simple_ascii.txt');
makeSimpleNamespacedSerialized('http://www.δοκιμή.com/κάτι', 'serialized_namespace_simple_utf8.txt');

makeComplexSerialized('http://www.example.com/mysite', 'serialized_complex_ascii.txt');
makeComplexSerialized('http://www.δοκιμή.com/κάτι', 'serialized_complex_utf8.txt');

makeComplexNamespacedSerialized('http://www.example.com/mysite', 'serialized_namespace_complex_ascii.txt');
makeComplexNamespacedSerialized('http://www.δοκιμή.com/κάτι', 'serialized_namespace_complex_utf8.txt');

makeSerializedArray('serialized_array.txt');