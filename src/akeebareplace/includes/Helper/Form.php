<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\Helper;


use Akeeba\Replace\WordPress\Model\Replace;
use Akeeba\Replace\WordPress\MVC\Model\Model;
use Akeeba\Replace\WordPress\MVC\Uri\Uri;

/**
 * HTML form helpers
 *
 * @package Akeeba\Replace\WordPress\Helper
 */
abstract class Form
{
	/**
	 * Returns an HTML select for the supported database collations
	 *
	 * @param   string  $name      Name of the HTML form field
	 * @param   string  $id        ID of the HTML form field
	 * @param   string  $selected  Currently selected value
	 *
	 * @return  string
	 */
	public static function selectCollation($name, $id, $selected = '')
	{
		$model      = new Replace();
		$collations = $model->getCollations();

		$options = [
			'' => __('(no change)', 'akeebareplace')
		];

		array_walk($collations, function ($collation) use (&$options) {
			$options[$collation] = $collation;
		});

		$html = '<select name="' . $name . '" id="' . $id . '">' . "\n";

		array_walk($options, function($text, $value) use (&$html, $selected) {
			$attributes = ($selected == $value) ? ' selected="selected"' : '';
			$html .= "\t<option value=\"$value\"$attributes>$text</option>\n";
		});

		$html .= "</select>\n";

		return $html;
	}

	/**
	 * Returns a SELECT element for excluding tables
	 *
	 * @param   string  $name       The name attribute of the element
	 * @param   string  $id         The id attribute of the element
	 * @param   array   $selected   Currently selected tables. Must use the full (real) name of the table.
	 * @param   bool    $allTables  Should I list all tables, even those which do not have the same prefix as the site?
	 *
	 * @return  string
	 */
	public static function selectExcludeTables($name, $id, $selected = [], $allTables = false)
	{
		/** @var Replace $model */
		$model  = Model::getInstance('Replace');
		$tables = $model->getDatabaseTables($allTables);

		array_unshift($tables, '');

		$html = '<select name="' . $name . '" id="' . $id . '" multiple="multiple" size="10">' . "\n";

		array_walk($tables, function ($value) use (&$html, $selected) {
			$attributes = in_array($value, $selected) ? ' selected="selected"' : '';
			$text       = empty($value) ? __('(none)', 'akeebareplace') : $value;
			$html       .= "\t<option value=\"$value\"$attributes>$text</option>\n";
		});

		$html .= "</select>\n";

		return $html;
	}

	/**
	 * Creates the HTML code for pagination
	 *
	 * @param   int      $total      Total amount of records
	 * @param   int      $limitStart Initial offset
	 * @param   int|null $limit      Records per page
	 * @param   string   $topBottom  Suffix for the selector's ID ('top' or 'bottom')
	 *
	 * @return string
	 */
	public static function pagination($total, $limitStart, $limit = null, $topBottom = 'top')
	{
		// If no limit has been supplied, fetch it from user options
		if (!$limit)
		{
			$limit = WordPress::get_page_limit();
		}

		// Avoid division by zero errors if we have no limits
		$total_pages  = 0;
		$current_page = 0;

		if ($limit)
		{
			$total_pages  = (int) max(1, ceil((float) $total / (float) $limit));
			$current_page = (int) ceil((float) ($limitStart + 1) / (float) $limit);
		}

		if (!$current_page)
		{
			$current_page = 1;
		}

		// If the user set the value of 0 it means that no pagination should be applied
		if ($limit === 0)
		{
			$current_page = 1;
			$total_pages  = 1;
		}

		$page_links    = array();
		$disable_first = ($current_page == 1) || ($current_page == 2);
		$disable_prev  = ($current_page == 1);
		$disable_next  = ($current_page == $total_pages);
		$disable_last  = ($current_page == $total_pages) || ($current_page == ($total_pages - 1));

		// Get a reference to the current URL and null some var
		$base_url      = Uri::getInstance();
		$base_url->setVar('paged', null);
		$base_url->setVar('task', null);

		if ($disable_first)
		{
			$page_links[] = '<span class="tablenav-pages-navspan">&laquo;</span>';
		}
		else
		{
			$url = clone $base_url;
			$url->setVar('limitstart', 0);
			$click_url = $url->toString();
			$page_links[] = '<a class="first-page" href="'.$click_url.'"><span>&laquo;</span></a>';
		}

		if ($disable_prev)
		{
			$page_links[] = '<span class="tablenav-pages-navspan">&lsaquo;</span>';
		}
		else
		{
			$new_limit = ($limitStart - $limit);

			if (!$new_limit)
			{
				$new_limit = 0;
			}

			$url = clone $base_url;
			$url->setVar('limitstart', $new_limit);
			$click_url = $url->toString();
			$page_links[] = '<a class="prev-page" href="'.$click_url.'"><span>&lsaquo;</span></a>';
		}

		$html_current_page  = '<input class="current-page" id="current-page-selector-' . $topBottom . '" type="text" value="'.$current_page.'" size="'.strlen( $total_pages).'"/>';
		$html_current_page .= '<span class="tablenav-paging-text">';

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
		$page_links[]     = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . '</span></span>';

		if ($disable_next)
		{
			$page_links[] = '<span class="tablenav-pages-navspan">&rsaquo;</span>';
		}
		else
		{
			$new_limit = ($limitStart + $limit);

			if ($new_limit > $total)
			{
				$new_limit = $total;
			}

			$url = clone $base_url;
			$url->setVar('limitstart', $new_limit);
			$click_url = $url->toString();
			$page_links[] = '<a class="next-page" href="'.$click_url.'"><span>&rsaquo;</span></a>';
		}

		if ($disable_last)
		{
			$page_links[] = '<span class="tablenav-pages-navspan">&raquo;</span>';
		}
		else
		{
			// Take the second to last page and multiply for the limit, so we will start from the last one
			$new_limit = ($total_pages  - 1 ) * $limit;

			if ($new_limit > $total)
			{
				$new_limit = $total - $limit;
			}

			$url = clone $base_url;
			$url->setVar('limitstart', $new_limit);
			$click_url = $url->toString();
			$page_links[] = '<a class="last-page" href="'.$click_url.'"><span>&raquo;</span></a>';
		}

		$output  = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total ), number_format_i18n( $total ) ) . '</span>';
		$output .= '<span class="pagination-links">'. implode("\n", $page_links ) . '</span>';

		$html    = '<div class="tablenav-pages">'.$output.'</div>';

		return $html;
	}

	/**
	 * Format a date/time string
	 *
	 * @param   string|int  $date    The date or UNIX timestamp to format.
	 * @param   string      $format  The format string to apply
	 *
	 * @return  string
	 */
	public static function formatDate($date = 'now', $format = 'Y-m-d H:i:s T')
	{
		$tzString = get_option('timezone_string', 'UTC');

		if ($date == 'now')
		{
			$date = date('Y-m-d H:i:s T');
		}

		if (is_numeric($date))
		{
			$date = date('Y-m-d H:i:s T', $date);
		}

		try
		{
			$tz = new \DateTimeZone($tzString);
		}
		catch (\Exception $e)
		{
			$tz = new \DateTimeZone('UTC');
		}

		try
		{
			$dateTime = new \DateTime($date);

			return $dateTime->setTimezone($tz)->format($format);
		}
		catch (\Exception $e)
		{
			return $date;
		}
	}
}