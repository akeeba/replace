<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

/** @var \Akeeba\Replace\WordPress\View\Replace\Html $this */

?>
<div id="akeebareplace-retry-panel" style="display: none">
	<div class="akeeba-panel--warning">
		<header class="akeeba-block-header">
			<h3>
				<?php _e('Replacement operation has halted but will resume automatically', 'akeebareplace') ?>
			</h3>
		</header>
		<div id="akeebareplace-retryframe">
			<p>
				<?php _e('The replacement operation has been halted because an error was detected. However, Akeeba Replace will attempt to resume the replacement operation. If you do not want to resume the replacement operation please click the Cancel button below.', 'akeebareplace') ?>
			</p>
			<p>
				<strong>
					<?php _e('The replacement operation will resume in', 'akeebareplace') ?>
					<span id="akeeba-retry-timeout">0</span>
				</strong>
				<br/>
				<button class="akeeba-btn--red--small" onclick="akeeba.replace.cancelResume(); return false;">
					<span class="akion-android-cancel"></span>
					<?php _e('Cancel', 'akeebareplace') ?>
				</button>
				<button class="akeeba-btn--green--small" onclick="akeeba.replace.resumeReplacement(); return false;">
					<span class="akion-ios-redo"></span>
					<?php _e('Resume', 'akeebareplace') ?>
				</button>
			</p>

			<p><?php _e('The last error message was:', 'akeebareplace') ?></p>
			<p id="akeebareplace-error-message-retry"></p>
		</div>
	</div>
</div>