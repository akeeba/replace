<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests;


use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

trait vfsAware
{
	/**
	 * Virtual filesystem, used for testing
	 *
	 * @var vfsStreamDirectory
	 */
	protected $root;

	/**
	 * Execute this on the test case's setUp() method
	 */
	protected function setUp_vfsAware()
	{
		$this->root = vfsStream::setup('testing');
	}

}