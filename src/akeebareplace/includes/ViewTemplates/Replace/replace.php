<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

use Akeeba\Replace\WordPress\Helper\Application;

/** @var \Akeeba\Replace\WordPress\View\Replace\Html $this */

wp_enqueue_script('akeebareplace-replace', plugins_url('/js/replace.js', AKEEBA_REPLACE_SELF), ['akeebareplace-system'], Application::getMediaVersion());

$actionURL       = addcslashes(html_entity_decode($this->actionURL), "\\'");
$logURL          = addcslashes($this->logURL, "\\'");
$lblLastResponse = __('Last response from the server: %s seconds ago.', 'akeebareplace');
$js              = <<< JS
akeeba.System.documentReady(function() {
	function akeebaReplaceSetupReplacements()
	{
		if ((typeof(akeeba) === 'undefined') || typeof(akeeba.replace) === 'undefined')
		{
			setTimeout(akeebaReplaceSetupReplacements, 500);

			return;
		}
		
		akeeba.System.params.AjaxURL = '$actionURL';
		akeeba.replace.logUrl = '$logURL';

		akeeba.replace.strings['lblLastResponse'] = '$lblLastResponse';
		
		akeeba.System.addEventListener(document.getElementById('akeebareplace-button-start'), 'click', akeeba.replace.start);
	}

	akeebaReplaceSetupReplacements();
});
JS;

wp_add_inline_script('akeebareplace-replace', $js);
?>

<?= $this->getRenderedTemplate('Common', 'header'); ?>
<?= $this->getRenderedTemplate('Common', 'errorDialog'); ?>
<?= $this->getRenderedTemplate('', 'initial_confirmation'); ?>
<?= $this->getRenderedTemplate('', 'progress'); ?>
<?= $this->getRenderedTemplate('', 'complete'); ?>
<?= $this->getRenderedTemplate('', 'warnings'); ?>
<?= $this->getRenderedTemplate('', 'retry'); ?>
<?= $this->getRenderedTemplate('', 'error'); ?>