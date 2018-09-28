<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 * Error handler page
 */
?>
<div class="wrap">
	<h1>
		<?php _e('Akeeba Replace - An error has occurred', 'akeebareplace') ?>
	</h1>
	<p>
		<?php _e('The following error occurred while we were trying to process your request.', 'akeebareplace') ?>
	</p>
	<p>
		<?php echo $e->getCode() ?> :: <?php echo $e->getMessage() ?>
	</p>
	<p>
		<?php _e('Please check the documentation for further information or ask for technical support on the plugin\'s listing in the WordPress Plugin Directory.' , 'akeebareplace') ?>
	</p>

	<h2>
		<?php _e('Technical Information', 'akeebareplace') ?>
	</h2>
	<p>
		<?php _e('If you want to get technical support, please provide the following information and a short description of what you were trying to do when the error occurred.', 'akeebareplace') ?>

	</p>
	<table>
		<tr>
			<td><strong>Code</strong></td>
			<td><?php echo $e->getCode() ?></td>
		</tr>
		<tr>
			<td><strong>Message</strong></td>
			<td><?php echo $e->getMessage() ?></td>
		</tr>
		<tr>
			<td><strong>Type</strong></td>
			<td><?php echo get_class($e) ?></td>
		</tr>
		<tr>
			<td><strong>File</strong></td>
			<td><?php echo $e->getFile() ?></td>
		</tr>
		<tr>
			<td><strong>Line</strong></td>
			<td><?php echo $e->getLine() ?></td>
		</tr>
		<tr>
			<td><strong>Trace</strong></td>
			<td><pre><?php echo $e->getTraceAsString() ?></pre></td>
		</tr>
	</table>
</div>
