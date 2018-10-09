<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

/** @var \Akeeba\Replace\WordPress\View\Replace\Html $this */

$dryRun     = !$this->configuration->isLiveMode();
$hasOutput  = $this->configuration->getOutputSQLFile() != '';
$hasBackups = $this->configuration->getBackupSQLFile() != '';
?>
<div id="akeebareplace-complete" style="display: none">
	<div class="akeeba-panel--success">
		<header class="akeeba-block-header">
			<h3>
				<?php _e('The replacement operation has completed successfully' ,'akeebareplace') ?>
			</h3>
		</header>

		<div id="akeebareplace-finishedframe">
			<p>
				<?php if ($dryRun && $hasOutput): ?>
					<?php _e('You chose not to apply the replacements to your database. Nothing has changed on your site.', 'akeebareplace') ?>
					<?php _e('All changes have been written to a SQL file, though. You can apply it manually using phpMyAdmin or a similar tool.', 'akeebareplace') ?>
				<?php elseif ($dryRun): ?>
					<?php _e('You chose not to apply the replacements to your database. Nothing has changed on your site.', 'akeebareplace') ?>
					<?php _e('You will need to check the log file to see which replacements would take place.', 'akeebareplace') ?>
				<?php elseif($hasBackups): ?>
					<?php _e('The replacements have been applied to your database,', 'akeebareplace') ?>
					<?php _e('Should the results be unsatisfactory please restore the database from the backup taken automatically, per the instructions in the documentation.', 'akeebareplace') ?>
				<?php else: ?>
					<?php _e('The replacements have been applied to your database,', 'akeebareplace') ?>
					<strong>
						<?php _e('You chose not to take any automatic backups. Should the results be unsatisfactory you will need to revert them by manually editing your database.', 'akeebareplace') ?>
					</strong>
				<?php endif; ?>
			</p>

			<a class="akeeba-btn--primary--big" href="<?php echo htmlentities($this->manageURL) ?>">
				<span class="akion-ios-list"></span>
				<?php _e('Manage replacement jobs' ,'akeebareplace') ?>
			</a>
			<a class="akeeba-btn--grey" href="<?php echo htmlentities($this->logURL) ?>">
				<span class="akion-ios-search-strong"></span>
				<?php _e('View the log file' ,'akeebareplace') ?>
			</a>
		</div>
	</div>
</div>