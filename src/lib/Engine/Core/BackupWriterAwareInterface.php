<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\Engine\Core;

use Akeeba\Replace\Writer\WriterInterface;

interface BackupWriterAwareInterface
{
	/**
	 * Returns the reference to the class' backup writer object
	 *
	 * @return  WriterInterface
	 */
	public function getBackupWriter();
}