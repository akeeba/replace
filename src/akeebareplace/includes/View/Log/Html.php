<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\View\Log;

use Akeeba\Replace\WordPress\MVC\View\Html as BaseView;

class Html extends BaseView
{
	/**
	 * The Job ID to display the log for
	 *
	 * @var  int
	 */
	public $logId = 0;

	/**
	 * Total size of the log in bytes
	 *
	 * @var  int
	 */
	public $logSize = 0;

	/**
	 * Is the log too big to display?
	 *
	 * @var  bool
	 */
	public $logTooBig = false;
}