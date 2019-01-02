<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

/** @var \Akeeba\Replace\WordPress\View\Replace\Html $this */

?>
<div id="akeebareplace-error-panel" style="display: none">
	<div class="akeeba-panel--red">
		<header class="akeeba-block-header">
			<h3>
				<?php _e('The replacement operation has failed', 'akeebareplace') ?>
			</h3>
		</header>

		<div id="akeebareplace-errorframe">
			<p>
				<?php _e('The replacement operation has been halted because an error was detected.<br />The last error message was:', 'akeebareplace') ?>
			</p>
			<p id="akeebareplace-error-message"></p>

			<p>
				<?php _e('Please click the \'View Log\' button below to view the Akeeba Replace log file for further information.', 'akeebareplace') ?>
			</p>

			<div class="akeeba-block--info" id="akeebareplace-error-panel-troubleshooting">
				<p>
					<?php echo sprintf(__('We strongly recommend going through the step-by-step instructions in our <a href="%s">troubleshooting documentation</a> to easily resolve this issue yourself.', 'akeebareplace'), $this->troubleshootingURL) ?>
				</p>
				<p>
					<?php echo sprintf(__('If you ask for technical support please remember to ZIP and attach your <a href="%s">log file</a> in your post to help us help you faster.', 'akeebareplace'), htmlentities($this->logURL)) ?>
				</p>
			</div>

			<a class="akeeba-btn--primary" href="<?php echo $this->troubleshootingURL ?>">
				<span class="akion-ios-book"></span>
				<?php _e('Troubleshooting documentation', 'akeebareplace') ?>
			</a>

			<a class="akeeba-btn--grey" href="<?php echo htmlentities($this->logURL) ?>">
				<span class="akion-ios-search-strong"></span>
				<?php _e('View Log', 'akeebareplace') ?>
			</a>
		</div>
	</div>
</div>