<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */
?>
<div class="wrap">
	<h2>
		Akeeba Replace
	</h2>
	<form action="options.php" method="post">
		<?php
		settings_fields('akeebareplace_options');
		do_settings_sections('akeebareplace_options');
		?>

		<?php submit_button( __( 'Save Changes' ), 'primary', 'Update' ); ?>
	</form>
</div>
