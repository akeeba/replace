<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

/** @var \Akeeba\Replace\WordPress\View\Replace\Html $this */
?>
<div id="akeebareplace-progress-pane" style="display: none">
	<div class="akeeba-block--info">
		<?php _e('Please do not close this browser tab, let your device sleep or disconnect from the network while the replacement operation is in progress.', 'akeebareplace') ?>
	</div>

	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>
				<?php _e('Replacement in progress', 'akeebareplace') ?>
			</h3>
		</header>

		<div id="akeebareplace-progress-content">
			<div id="akeebareplace-status" class="akeebareplace-steps-container">
				<div id="akeebareplace-step">
					<?php _e('Please wait. Replacement is in progress.', 'akeebareplace') ?>
				</div>
				<div id="akeebareplace-substep">
					<?php _e('You will see more information here as the replacement progresses.', 'akeebareplace') ?>
				</div>
			</div>
			<div id="akeebareplaceresponse-timer">
				<div class="akeebareplace-text"></div>
			</div>
		</div>
	</div>
</div>