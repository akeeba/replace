<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\MVC\View;

use Akeeba\Replace\WordPress\Helper\Application;

/**
 * A View class for raw output
 *
 * @package Akeeba\Replace\WordPress\MVC\View
 */
abstract class Raw extends View
{
	/**
	 * Should I wrap the output in a div.akeeba-renderer-fef and load FEF?
	 *
	 * @var  bool
	 */
	protected $useFEF = true;

	/**
	 * Runs before rendering the body of the application output.
	 *
	 * For example, it's used to rendered any enqueued messages.
	 *
	 * @return  string
	 */
	public function preRender()
	{
		$class = $this->useFEF ? 'akeeba-renderer-fef akeeba-wp' : 'wrap';

		return <<< HTML
<html>
<head>
<title></title>
</head>
<body>
<div class="$class">
HTML;
	}

	/**
	 * Runs after rendering the body of the application output.
	 *
	 * @return  string
	 */
	public function afterRender()
	{
		$this->loadFEFStylesheet();

		return <<< HTML
</div>
</body>
</html>
HTML;
	}

	/**
	 * Loads the FEF stylesheet
	 */
	protected function loadFEFStylesheet()
	{
		wp_enqueue_style('fef', plugins_url('/fef/css/fef-wordpress.min.css', AKEEBA_REPLACE_SELF), [], Application::getMediaVersion());
	}

	protected function loadFEFJS($script = '')
	{
		if (is_array($script))
		{
			array_walk($script, [$this, 'loadFEFJS']);

			return;
		}

		if (!in_array($script, ['dropdown', 'menu', 'tabs']))
		{
			return;
		}

		wp_enqueue_script('fef-' . $script, plugins_url('/fef/js/' . $script . '.js', AKEEBA_REPLACE_SELF), [], Application::getMediaVersion());
	}

	/**
	 * Load a template and return its rendered result
	 *
	 * @param   string  $view         The view where the view template belongs to
	 * @param   string  $layout       The base name of the view template
	 * @param   string  $subTemplate  The name of the view subtemplate
	 * @param   array   $extraVars    An array of additional variables to make visible in the view template file
	 *
	 * @return  bool
	 */
	public function getRenderedTemplate($view = null, $layout = null, $subTemplate = '', $extraVars = [])
	{
		$view        = empty($view) ? $this->name : $view;
		$layout      = empty($layout) ? $this->layout : $layout;
		$includeFile = $this->getViewTemplatePath($view, $layout, $subTemplate);

		if (!file_exists($includeFile))
		{
			$errorLayout      = $layout . (empty($subTemplate) ? '' : ('_' . $subTemplate));
			$technicalDetails = '';

			if (WP_DEBUG)
			{
				$traceInfo = "(Not available on this server)";
				$trace = "";

				if (function_exists('debug_print_backtrace'))
				{
					$traceInfo = "(See below)";
					@ob_start();
					debug_print_backtrace();
					$trace = "\n" . @ob_get_clean();
				}


				$technicalDetails = <<< HTML
<h4>
	Technical details:
</h4>
<pre>
Current View     : {$this->name}
Current Layout   : {$this->layout}
Requested View   : $view
Requested Layout : $layout
Sub-template     : $subTemplate
Requested Path   : $includeFile
Stack Trace      : $traceInfo
$trace
</pre>

HTML;
			}

			$class       = WP_DEBUG ? 'akeeba-panel--danger' : 'akeeba-block--failure';
			$headerClass = WP_DEBUG ? 'akeeba-block-header' : '';

			return <<< HTML
<div class="notice notice-error $class">
	<header class="$headerClass">
		<h3>Cannot load View Template “$view/{$errorLayout}”</h3>
	</header>
	$technicalDetails
</div>
HTML;

		}

		@ob_start();

		if (!empty($extraVars))
		{
			extract($extraVars);
		}

		require_once $includeFile;
		$foo = @ob_get_contents();
		@ob_end_clean();

		return $foo;
	}

}
