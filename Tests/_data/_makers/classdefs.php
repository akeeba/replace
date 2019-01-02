<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace McTestface
{
	class SampleOne
	{
		private $somePrivateString = 'http://www.example.com/mysite';

		public $spanner = 'This is the "monkey wrench" with escaped double quotes και Ελληνικά and special \r\n\t characters';

		private $somePrivateArray = [
			'foo' => 'bar',
			'baz' => '/mysite',
		];

		public $somePublicInteger = 123;

		protected $protectedNull;

		public $publicThingie;

		public function __construct($withURL = null)
		{
			if (!empty($withURL))
			{
				$this->somePrivateString        = $withURL;
				$slashPos                       = strpos($withURL, '/', 8);
				$this->somePrivateArray['baz'] = substr($withURL, $slashPos);
			}
		}
	}

	class SampleSubclass extends SampleOne
	{
		public $somePublicInteger = 890;

		public $anotherPublicFloat = 3.1415;
	}
}

namespace
{
	class SampleTwo
	{
		private $somePrivateString = 'http://www.example.com/mysite';

		public $spanner = 'This is the "monkey wrench" with escaped double quotes και Ελληνικά and special \r\n\t characters';

		private $somePrivateArray = [
			'foo' => 'bar',
			'baz' => '/mysite',
		];

		public $somePublicInteger = 123;

		protected $protectedNull;

		public $publicThingie;

		public function __construct($withURL = null)
		{
			if (!empty($withURL))
			{
				$this->somePrivateString        = $withURL;
				$slashPos                       = strpos($withURL, '/', 8);
				$this->somePrivateArray['baz'] = substr($withURL, $slashPos);
			}
		}
	}

	class SampleTwoSubclass extends SampleTwo
	{
		public $somePublicInteger = 890;

		public $anotherPublicFloat = 3.1415;
	}


	class InnerThing
	{
		public $nestedBit;

		public $replaceMe = 'http://www.example.com/mysite/some/other/path';

		public $dontReplaceMe = 'some/other/path';

		public $anotherReplacedThing = 'http://www.example.com';

		public $sneakyReplacedThingie = '/mysite/sneaky/thing';
	}
}