<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

/** @var \Akeeba\Replace\WordPress\View\Replace\Html $this */
$dryRun     = !$this->configuration->isLiveMode();
$hasOutput  = $this->configuration->getOutputSQLFile() != '';
$hasBackups = $this->configuration->getBackupSQLFile() != '';
?>

<div class="akeeba-panel--warning" id="akeebareplace-last-chance">
	<header class="akeeba-block-header">
		<h3><?php _e('Start replacing?', 'akeebareplace')?></h3>
	</header>
	<p>
		<?php if ($dryRun): ?>
			<?php _e('You are about to start replacing data, without applying these changes to your database. You will need to check the log file to see which replacements would take place.', 'akeebareplace') ?>
		<?php elseif($hasBackups): ?>
			<?php _e('You are about to start replacing data in your database. Depending on your settings this may cause your site to become unresponsive.', 'akeebareplace') ?>
			<?php _e('In this case, you will need to restore the database from a backup, per the instructions in the documentation.', 'akeebareplace') ?>
		<?php else: ?>
			<?php _e('You are about to start replacing data in your database. Depending on your settings this may cause your site to become unresponsive.', 'akeebareplace') ?>
			<strong>
				<?php _e('You have indicated that no backups are to be taken. If something breaks you will have to fix it on your own. By continuing you assume all responsibility for your actions and accept that you will receive ABSOLUTELY NO SUPPORT WHATSOEVER.', 'akeebareplace') ?>
			</strong>
		<?php endif; ?>

	</p>
	<p>
		<?php _e('Shall I continue?', 'akeebareplace') ?>
	</p>
	<p>
		<button type="button" class="akeeba-btn--green--big" id="akeebareplace-button-start">
			<span class="akion-play"></span>
			<?php _e('Continue', 'akeebareplace') ?>
		</button>
		<a href="<?= htmlentities($this->cancelURL) ?>" class="akeeba-btn--red">
			<span class="akion-chevron-left"></span>
			<?php _e('Go back', 'akeebareplace') ?>
		</a>
	</p>
</div>
