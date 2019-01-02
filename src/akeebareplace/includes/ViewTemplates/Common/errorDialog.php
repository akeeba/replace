<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

?>
<div id="errorDialog" tabindex="-1" role="dialog" aria-labelledby="errorDialogLabel" aria-hidden="true"
     style="display:none;">
	<div class="akeeba-renderer-fef">
		<h4 id="errorDialogLabel">
			<?php _e('AJAX Error', 'akeebareplace'); ?>
		</h4>

		<p>
			<?php _e('An error has occurred while waiting for an AJAX response:', 'akeebareplace'); ?>
		</p>
		<pre id="errorDialogPre">
	</div>
</div>
