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

<?php echo $this->getRenderedTemplate('Common', 'errorDialog'); ?>

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
		<button type="button" class="akeeba-btn--green--big">
			<span class="akion-play"></span>
			<?php _e('Continue', 'akeebareplace') ?>
		</button>
		<a href="<?php echo htmlentities($this->cancelURL) ?>" class="akeeba-btn--red">
			<span class="akion-chevron-left"></span>
			<?php _e('Go back', 'akeebareplace') ?>
		</a>
	</p>
</div>

<div id="akeebareplace-progress-pane" style="display: none">
	<div class="akeeba-block--info">
		<?php _e('Replacement in progress. Please do not close this browser tab, do not out your device to sleep and do not disconnect from the network to prevent abnormal termination.', 'akeebareplace'); ?>
	</div>

	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>
				<?php _e('Replacement Progress', 'akeebareplace') ?>
			</h3>
		</header>

		<div id="akeebareplace-progress-content">
			<div id="akeebareplace-status" class="backup-steps-container">
				<div id="akeebareplace-step"></div>
				<div id="akeebareplace-substep"></div>
			</div>
			<div id="akeebareplaceresponse-timer">
				<div class="color-overlay"></div>
				<div class="text"></div>
			</div>
		</div>
		<span id="ajax-worker"></span>
	</div>
</div>

<div id="akeebareplace-complete" style="display: none">
	<div class="akeeba-panel--success">
		<header class="akeeba-block-header">
			<h3>
				<?php _e('Replacement Complete', 'akeebareplace') ?>
			</h3>
		</header>

		<div id="finishedframe">
			<?php if ($dryRun): ?>
			<p>
				<?php _e('The replacements operation has completed in Dry Run mode. This means that no changes were applied to your database.', 'akeebareplace') ?>
			</p>
			<p>
				<?php if ($hasOutput): ?>
					<?php _e('The replacements have been stored as one or more SQL files. Please return to the main page to download the SQL files.', 'akeebareplace') ?>
				<?php else: ?>
					<?php _e('You need to look at the log file to see which actions would have taken place.', 'akeebareplace') ?>
				<?php endif; ?>
			</p>
			<?php else: ?>
				<p>
					<?php _e('The replacements operation has completed in Live Mode. This means that the changes have been applied to your database.', 'akeebareplace') ?>
				</p>
				<?php if ($hasBackups): ?>
				<p>
						<?php _e('A backup copy of all changed data has been saved. If you regret your replacements or your site does not work properly please apply the backups to your database per the documentation.', 'akeebareplace') ?>
				</p>
				<?php endif; ?>
				<?php if ($hasOutput): ?>
				<p>
						<?php _e('The replacements have also been stored as one or more SQL files. Please return to the main page to download the SQL files.', 'akeebareplace') ?>
				</p>
				<?php endif; ?>
			<?php endif; ?>

			<p>
				<a class="akeeba-btn--primary--big" href="<?php echo htmlentities($this->cancelURL) ?>">
					<span class="akion-chevron-left"></span>
					<?php _e('Go back', 'akeebareplace') ?>
				</a>
				<a class="akeeba-btn--grey" id="ab-viewlog-success" href="<?php echo htmlentities($this->logURL) ?>">
					<span class="akion-ios-search-strong"></span>
					<?php _e('View log', 'akeebareplace') ?>
				</a>
			</p>
		</div>
	</div>
</div>

<div id="akeebareplace-warnings-panel" style="display:none">
	<div class="akeeba-panel--warning">
		<header class="akeeba-block-header">
			<h3><?php _e('Warnings', 'akeebareplace') ?></h3>
		</header>
		<div id="warnings-list">
		</div>
	</div>
</div>

<div id="akeebareplace-retry-panel" style="display: none">
	<div class="akeeba-panel--warning">
		<header class="akeeba-block-header">
			<h3>
				<?php _e('Replacement halted and will resume automatically', 'akeebareplace') ?>
			</h3>
		</header>
		<div id="retryframe">
			<p><?php _e('The replacement operation has been halted because an error was detected. However, Akeeba Replace will attempt to resume the replacement operation. If you do not want to resume the replacement operation please click the Cancel button below.', 'akeebareplace') ?></p>
			<p>
				<strong>
					<?php
					echo sprintf(__('The repalcement operation will resume in %s seconds', 'akeebareplace'), '<span id="akeeba-retry-timeout">0</span>')
					?>
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

			<p><?php _e('The last message was:', 'akeebareplace') ?></p>
			<p id="backup-error-message-retry">
			</p>
		</div>
	</div>
</div>

<div id="error-panel" style="display: none">
	<div class="akeeba-panel--red">
		<header class="akeeba-block-header">
			<h3>
				<?php _e('The replacement has failed', 'akeebareplace') ?>
			</h3>
		</header>

		<div id="errorframe">
			<p>
				<?php _e('The replacement operation is halted because an error was detected. The last error message was:', 'akeebareplace') ?>
			</p>
			<p id="backup-error-message">
			</p>

			<p>
				<?php _e('Please consult the log below for hints on what may be going on.', 'akeebareplace') ?>
			</p>

			<div class="akeeba-block--info" id="error-panel-troubleshooting">
				<p>
					<?php echo sprintf(__('We strongly recommend checking the <a href="%s">troubleshooting instructions</a> to find a solution to your issue.', 'akeebareplace'), '#') ?>
				</p>
			</div>

			<button class="akeeba-btn--primary" onclick="window.location='#'; return false;">
				<span class="akion-ios-book"></span>
				<?php _e('Troubleshooting instructions', 'akeebareplace') ?>
			</button>

			<button id="ab-viewlog-error" class="akeeba-btn-grey" onclick="window.location='<?php echo htmlentities($this->logURL) ?>'; return false;">
				<span class="akion-ios-search-strong"></span>
				<?php _e('View log', 'akeebareplace') ?>
			</button>
		</div>
	</div>
</div>