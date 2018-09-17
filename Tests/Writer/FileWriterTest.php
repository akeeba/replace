<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Tests\Writer;

use Akeeba\Replace\Writer\FileWriter;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

class FileWriterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Virtual filesystem, used for testing
	 *
	 * @var vfsStreamDirectory
	 */
	private $root;

	protected function setUp()
	{
		parent::setUp();

		$this->root = vfsStream::setup('testing');
	}

	/**
	 * Test __construct. Use a brand new file which does not exist.
	 *
	 * @return void
	 */
	public function testConstructWithNewFile()
	{
		$filePath = $this->root->url() . '/foobar.txt';
		$dummy    = new FileWriter($filePath, false);

		$this->assertEquals($filePath, $this->getObjectAttribute($dummy, 'filePath'));
		$this->assertEquals(0, $this->getObjectAttribute($dummy, 'numParts'));
	}

	/**
	 * Test __construct. Try writing to a folder which is not writeable.
	 *
	 * @return void
	 */
	public function testConstructWithUnwriteableFolder()
	{
		vfsStream::newDirectory('baz', 0600)
			->chown(vfsStream::OWNER_USER_2)
			->at($this->root);

		$filePath = $this->root->url() . '/baz/foobar.txt';

		$this->expectException("RuntimeException");
		$this->expectExceptionMessage("Cannot open file “{$filePath}” for writing.");

		$dummy    = new FileWriter($filePath, false);
	}

	/**
	 * Test __construct. Use an existing file which is not writeable.
	 *
	 * @return void
	 */
	public function testConstructWithUnwriteableFile()
	{
		vfsStream::newFile('foobar.txt', 0600)
			->chown(vfsStream::OWNER_USER_2)
			->at($this->root);

		$filePath = $this->root->url() . '/foobar.txt';

		$this->expectException("RuntimeException");
		$this->expectExceptionMessage("Cannot open file “{$filePath}” for writing.");

		$dummy    = new FileWriter($filePath, false);
	}

	/**
	 * Test __construct. Use an existing multipart file set, using $reset = true (delete existing files)
	 *
	 * @return void
	 */
	public function testConstructWithReset()
	{
		$extensions = ['txt', 't01', 't02', 't99'];

		foreach($extensions as $extension)
		{
			vfsStream::newFile('foobar.' . $extension, 0644)
				->withContent('Ignored')
				->at($this->root);
		}

		$filePath = $this->root->url() . '/foobar.txt';

		$dummy    = new FileWriter($filePath, true);

		$this->assertTrue($this->root->hasChild('foobar.txt'), 'Part #0 must exist.');
		$this->assertEquals(0, $this->root->getChild('foobar.txt')->size(), 'Part #0 must be truncated on reset');
		$this->assertFalse($this->root->hasChild('foobar.t01'), 'Part #1 must be deleted on reset.');
		$this->assertFalse($this->root->hasChild('foobar.t02'), 'Part #2 must be deleted on reset.');
		$this->assertTrue($this->root->hasChild('foobar.t99'), 'Non sequential part must not be deleted on reset.');
		$this->assertNotEquals(0, $this->root->getChild('foobar.t99')->size(), 'Non sequential part must not be truncated on reset');
	}

	/**
	 * Test __construct. Use an existing multipart file set, using $reset = false (DO NOT delete existing files)
	 *
	 * @return void
	 */
	public function testConstructWithExistingParts()
	{
		$extensions = ['txt', 't01', 't02', 't99'];

		foreach($extensions as $extension)
		{
			vfsStream::newFile('foobar.' . $extension, 0644)
				->withContent('Ignored')
				->at($this->root);
		}

		$filePath = $this->root->url() . '/foobar.txt';

		$dummy    = new FileWriter($filePath, false);

		$this->assertEquals(2, $this->getObjectAttribute($dummy, 'numParts'));

		$this->assertTrue($this->root->hasChild('foobar.txt'), 'Part #0 must exist.');
		$this->assertNotEquals(0, $this->root->getChild('foobar.txt')->size(), 'Part #0 must not be truncated');

		$this->assertTrue($this->root->hasChild('foobar.t01'), 'Part #1 must exist.');
		$this->assertNotEquals(0, $this->root->getChild('foobar.t01')->size(), 'Part #1 must not be truncated');

		$this->assertTrue($this->root->hasChild('foobar.t02'), 'Part #2 must exist.');
		$this->assertNotEquals(0, $this->root->getChild('foobar.t02')->size(), 'Part #2 must not be truncated');

		$this->assertTrue($this->root->hasChild('foobar.t99'), 'Non-sequential part must exist.');
		$this->assertNotEquals(0, $this->root->getChild('foobar.t99')->size(), 'Non-sequential part must not be truncated');
	}

	public function testGetListOfParts()
	{
		$extensions = ['txt', 't01', 't02', 't99'];

		foreach($extensions as $extension)
		{
			vfsStream::newFile('foobar.' . $extension, 0644)
				->withContent('Ignored')
				->at($this->root);
		}

		$filePath = $this->root->url() . '/foobar.txt';

		$dummy    = new FileWriter($filePath, false);

		$this->assertEquals(2, $this->getObjectAttribute($dummy, 'numParts'));

		$partsList = $dummy->getListOfParts();
		$expected = [
			$this->root->url() . '/foobar.txt',
			$this->root->url() . '/foobar.t01',
			$this->root->url() . '/foobar.t02',
		];

		$this->assertEquals($expected, $partsList);
	}

	public function testGetNumberOfParts()
	{
		$extensions = ['txt', 't01', 't02', 't99'];

		foreach($extensions as $extension)
		{
			vfsStream::newFile('foobar.' . $extension, 0644)
				->withContent('Ignored')
				->at($this->root);
		}

		$filePath = $this->root->url() . '/foobar.txt';

		$dummy    = new FileWriter($filePath, false);

		$this->assertEquals(2, $this->getObjectAttribute($dummy, 'numParts'));

		$this->assertEquals(3, $dummy->getNumberOfParts());

	}

	public function testSetMaxFileSize()
	{
		$filePath = $this->root->url() . '/foobar.txt';
		$dummy    = new FileWriter($filePath, false);

		$dummy->setMaxFileSize(-1);
		$this->assertEquals(0, $this->getObjectAttribute($dummy,'maxFileSize'), 'Negative part sizes must be squashed to zero');

		$dummy->setMaxFileSize(123);
		$this->assertEquals(123, $this->getObjectAttribute($dummy,'maxFileSize'), 'Positive part sizes must be accepted');

		$dummy->setMaxFileSize(0);
		$this->assertEquals(0, $this->getObjectAttribute($dummy,'maxFileSize'), 'Zero part size must be accepted');
	}

	/**
	 * Write a line without expecting trouble
	 *
	 * @return void
	 */
	public function testWriteLineNoProblem()
	{
		$filePath = $this->root->url() . '/foobar.txt';
		$dummy    = new FileWriter($filePath, false);

		$line = 'In PHP we trust';
		$dummy->writeLine($line, "\n");

		unset($dummy);

		$this->assertEquals($line . "\n", file_get_contents($filePath));
	}

	/**
	 * Write two lines, expect a new part file to be generated (line size smaller than part size)
	 *
	 * @return void
	 */
	public function testWriteLineChunked()
	{
		$filePath = $this->root->url() . '/foobar.txt';
		$filePart2 = $this->root->url() . '/foobar.t01';
		$dummy    = new FileWriter($filePath, false);
		$dummy->setMaxFileSize(25);

		$line = 'In PHP we trust';
		$dummy->writeLine($line, "\n");
		$dummy->writeLine($line, "\n");

		unset($dummy);

		$this->assertTrue($this->root->hasChild('foobar.t01'), 'Part #1 must exist.');
		$this->assertEquals($line . "\n", file_get_contents($filePath), 'Part #0 must have the first line.');
		$this->assertEquals($line . "\n", file_get_contents($filePart2), 'Part #1 must have the second line');

	}

	/**
	 * Write two lines, expect a new part file to be generated (line size bigger than part size)
	 *
	 * @return void
	 */
	public function testWriteLineChunkedVeryLong()
	{
		$filePath = $this->root->url() . '/foobar.txt';
		$filePart2 = $this->root->url() . '/foobar.t01';
		$dummy    = new FileWriter($filePath, false);
		$dummy->setMaxFileSize(25);

		$line = 'In PHP we trust and we get to write lines longer than the part size';
		$dummy->writeLine($line, "\n");
		$dummy->writeLine($line, "\n");

		unset($dummy);

		$this->assertTrue($this->root->hasChild('foobar.t01'), 'Part #1 must exist.');
		$this->assertEquals($line . "\n", file_get_contents($filePath), 'Part #0 must have the first line.');
		$this->assertEquals($line . "\n", file_get_contents($filePart2), 'Part #1 must have the second line');
	}


	/**
	 * Write a line, expect an error (filesystem ran out of free space)
	 *
	 * @return void
	 */
	public function testWriteLineRunningOutOfSpace()
	{
		$filePath = $this->root->url() . '/foobar.txt';
		$dummy    = new FileWriter($filePath, false);

		vfsStream::setQuota(10);

		$this->expectException('RuntimeException');
		$this->expectExceptionMessage('It looks like you run out of disk space. I tried writing 16 bytes, only 10 were written.');

		$line = 'In PHP we trust';
		$dummy->writeLine($line, "\n");
	}
}
