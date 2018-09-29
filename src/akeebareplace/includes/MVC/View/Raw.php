<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\MVC\View;

/**
 * A View class for raw output
 *
 * @package Akeeba\Replace\WordPress\MVC\View
 */
abstract class Raw extends View
{
	/**
	 * Runs before rendering the body of the application output.
	 *
	 * For example, it's used to rendered any enqueued messages.
	 *
	 * @return  string
	 */
	public function preRender()
	{
		return <<< HTML
<html>
<head>
<title></title>
</head>
<body>
HTML;
	}

	/**
	 * Runs after rendering the body of the application output.
	 *
	 * @return  string
	 */
	public function afterRender()
	{
		return <<< HTML
</body>
</html>
HTML;

	}

	/**
	 * Load a template and return its rendered result
	 *
	 * @param   string  $view         The view where the view template belongs to
	 * @param   string  $layout       The base name of the view template
	 * @param   string  $subTemplate  The name of the view subtemplate
	 *
	 * @return  bool
	 */
	public function getRenderedTemplate($view = null, $layout = null, $subTemplate = '')
	{
		$view        = empty($view) ? $this->name : $view;
		$layout      = empty($layout) ? $this->layout : $layout;
		$includeFile = $this->getViewTemplatePath($view, $layout, $subTemplate);

		if (!file_exists($includeFile))
		{
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

			return <<< HTML
<div class="notice notice-error">
	<h3>Cannot load View Template “$view/{$layout}”</h3>
	<p>
		The view template “{$layout}” was not found in view “{$view}”
	</p>
	$technicalDetails
</div>
HTML;

		}

		@ob_start();
		require_once $includeFile;
		$foo = @ob_get_contents();
		@ob_end_clean();

		return $foo;
	}

}
