<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\View\Replace;

use Akeeba\Replace\Engine\Core\Configuration;
use Akeeba\Replace\WordPress\Helper\WordPress;
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
	 * The URL to reset the replacements
	 *
	 * @var  string
	 */
	public $resetURL = '';

	/**
	 * The URL to go to view the latest log file
	 *
	 * @var  string
	 */
	public $logURL = '';

	/**
	 * The URL to fetch a list of all database tables
	 *
	 * @var  string
	 */
	public $tablesURL = '';

	/**
	 * The URL to go back to the jobs management page
	 *
	 * @var  string
	 */
	public $manageURL = '';

	/**
	 * TODO The URL to the troubleshooting documentation
	 *
	 * @var  string
	 */
	public $troubleshootingURL = '#';

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
			$url = WordPress::adminUrl('admin.php?page=akeebareplace&view=Replace&task=replace');
			$this->actionURL = wp_nonce_url($url, 'post_Replace_replace');
		}

		if (empty($this->tablesURL))
		{
			$url = WordPress::adminUrl('admin.php?page=akeebareplace&view=Replace&task=getTablesHTML');
			$this->tablesURL = html_entity_decode(wp_nonce_url($url, 'get_Replace_getTablesHTML'));
		}

		if (empty($this->cancelURL))
		{
			$this->cancelURL = WordPress::adminUrl('admin.php?page=akeebareplace');
		}

		if (empty($this->resetURL))
		{
			$this->resetURL = WordPress::adminUrl('admin.php?page=akeebareplace&view=Replace&reset=1');
		}

		$this->excludedColumns = $this->makeExcludedColumnsText();
	}

	/**
	 * Runs when the “replace” task is being executed
	 */
	public function onBeforeReplace()
	{
		$this->layout = 'replace';

		if (empty($this->actionURL))
		{
			$url = WordPress::adminUrl('admin.php?page=akeebareplace&view=Replace&task=ajax');
			$this->actionURL = wp_nonce_url($url, 'post_Replace_ajax');
		}

		if (empty($this->cancelURL))
		{
			$this->cancelURL = WordPress::adminUrl('admin.php?page=akeebareplace&view=Replace');
		}

		if (empty($this->logURL))
		{
			$this->logURL = WordPress::adminUrl('admin.php?page=akeebareplace&view=Log&latest=1');
		}

		if (empty($this->manageURL))
		{
			$this->manageURL = WordPress::adminUrl('admin.php?page=akeebareplace');
		}
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

				return $ret;
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