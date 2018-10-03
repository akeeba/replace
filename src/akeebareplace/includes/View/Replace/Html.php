<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\View\Replace;

use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\WordPress\MVC\View\Html as AbstractHtml;

class Html extends AbstractHtml
{
	/**
	 * The new job form action URL
	 *
	 * @var  string
	 */
	public $actionURL = '';

	/**
	 * The URL to go to when canceling the replacements
	 *
	 * @var  string
	 */
	public $cancelURL = '';

	/**
	 * The configuration for this job
	 *
	 * @var  Configuration
	 */
	public $configuration;

	/**
	 * Excluded columns, as an HTML string
	 *
	 * @var  string
	 */
	public $excludedColumns = '';

	/**
	 * Runs when the “newJob” task is being executed
	 */
	public function onBeforeNewJob()
	{
		$this->layout = 'new';

		if (empty($this->actionURL))
		{
			$url = admin_url('admin.php?page=akeebareplace&view=Replace&task=start');
			$this->actionURL = wp_nonce_url($url, 'post_Replace_start');
		}

		if (empty($this->cancelURL))
		{
			$this->cancelURL = admin_url('admin.php?page=akeebareplace');
		}

		$this->excludedColumns = $this->makeExcludedColumnsText();
	}

	/**
	 * Convert the internal representation of excluded columns into text the user can understand
	 *
	 * @return  string
	 */
	protected function makeExcludedColumnsText()
	{
		$exclusions = $this->configuration->getExcludeRows();

		if (empty($exclusions))
		{
			return '';
		}

		$returnValue = '';

		foreach ($exclusions as $table => $columns)
		{
			$theseColumns = array_reduce($columns, function ($ret, $column) use ($table) {
				if (!empty($ret))
				{
					$ret .= ', ';
				}

				$ret .= $table . '.' . $column;
			});

			if (!empty($returnValue))
			{
				$returnValue .= "\n";
			}

			$returnValue .= $theseColumns;
		}

		return $returnValue;
	}
}