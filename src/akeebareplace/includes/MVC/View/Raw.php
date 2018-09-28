<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

namespace Akeeba\Replace\WordPress\MVC\View;

use Akeeba\Replace\WordPress\MVC\Input\Filter;
use Akeeba\Replace\WordPress\MVC\Model\Model;
use Akeeba\Replace\WordPress\MVC\Model\ModelInterface;

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
	 * @param   string  $subTemplate
	 *
	 * @return  bool
	 */
	protected function getRenderedTemplate($subTemplate)
	{
		$includeFile = $this->getViewTemplatePath($this->layout, $subTemplate);

		if (!file_exists($includeFile))
		{
			return <<< HTML
<div class="notice notice-error">
	<h3>Cannot load View Template</h3>
	<p>
		The view template {$this->layout} was not found in view {$this->name}
	</p>
	<p>
		Technical details:
	</p>
	<pre>
View        : {$this->name}
Layout      : {$this->layout}
Sub-template: {$subTemplate}
Path        : {$includeFile}
	</pre>
</div>
HTML;

		}

		@ob_start();
		require_once $includeFile;
		$foo = @ob_end_clean();

		return $foo;
	}

}
