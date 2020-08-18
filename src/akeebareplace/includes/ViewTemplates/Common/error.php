<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

wp_enqueue_style('fef', plugins_url('/fef/css/fef-wordpress.min.css', AKEEBA_REPLACE_SELF), []);

/** @var Exception $e */
?>
<div class="akeeba-renderer-fef akeeba-wp">

	<div class="akeeba-panel--danger ">
		<header class="akeeba-block-header">
			<h2>
				<?php _e('Akeeba Replace - An error has occurred', 'akeebareplace') ?>
			</h2>
		</header>

		<h3>
			<span class="akeeba-label--red">
				<?= $e->getCode() ?>
			</span>
			 <?= $e->getMessage() ?>
		</h3>
		<p>
			<?php _e('The error above occurred while we were trying to process your request.', 'akeebareplace') ?>
		</p>
		<p>
			<?php _e('Please check the documentation for further information or ask for technical support on the plugin\'s listing in the WordPress Plugin Directory.' , 'akeebareplace') ?>
		</p>

		<h4>
			<?php _e('Technical Information', 'akeebareplace') ?>
		</h4>
		<p>
			<?php _e('If you want to get technical support, please provide the following information and a short description of what you were trying to do when the error occurred.', 'akeebareplace') ?>

		</p>
		<table class="akeeba-table--leftbold--striped">
			<tr>
				<td>Code</td>
				<td><?= $e->getCode() ?></td>
			</tr>
			<tr>
				<td>Message</td>
				<td><?= $e->getMessage() ?></td>
			</tr>
			<tr>
				<td>Type</td>
				<td><?= get_class($e) ?></td>
			</tr>
			<tr>
				<td>File</td>
				<td><?= $e->getFile() ?></td>
			</tr>
			<tr>
				<td>Line</td>
				<td><?= $e->getLine() ?></td>
			</tr>
			<tr>
				<td>Trace</td>
				<td><pre><?php
					$trace = $e->getTraceAsString();
					echo str_replace(rtrim(ABSPATH, '/\\'), '[ SITE ROOT ]', $trace);
				?></pre></td>
			</tr>
		</table>
	</div>
</div>