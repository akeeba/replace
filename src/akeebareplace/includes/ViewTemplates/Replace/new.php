<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

use Akeeba\Replace\WordPress\Helper\Application;
use Akeeba\Replace\WordPress\Helper\Form;

/** @var \Akeeba\Replace\WordPress\View\Replace\Html $this */

$effectiveLogLevel = $this->configuration->getMinLogLevel();
$effectiveLogLevel = ($this->configuration->getLogFile()) == '' ? 50 : $effectiveLogLevel;
$replacements      = $this->configuration->getReplacements();
$lblKey            = addcslashes(__('Replace this', 'akeebareplace'), "'\\");
$lblValue          = addcslashes(__('Replace with that', 'akeebareplace'), "'\\");
$lblDelete         = addcslashes(__('Delete this replacement', 'akeebareplace'), "'\\");

wp_enqueue_script('akeebareplace-editor', plugins_url('/js/editor.js', AKEEBA_REPLACE_SELF), ['akeebareplace-system'], Application::getMediaVersion());

$subheading = __('Set up a replacement job', 'akeebareplace');
?>

<?php echo $this->getRenderedTemplate('Common', 'header', '', ['subheading' => $subheading]); ?>
<?php echo $this->getRenderedTemplate('Common', 'errorDialog'); ?>

<form
        method="post"
        action="<?php echo $this->actionURL ?>"
        class="akeeba-form--horizontal">

    <div id="akeebareplace-replace-main" class="akeeba-panel--primary">
        <header class="akeeba-block-header">
            <h3>
				<?php _e('What to replace', 'akeebareplace') ?>
            </h3>
        </header>

        <div id="akeebareplaceTextboxEditor">
            <div class="akeeba-form-group">
                <label for="akeebareplaceTextboxFrom">
				    <?php _e('Replace this', 'akeebareplace') ;?>
                </label>
                <textarea id="akeebareplaceTextboxFrom" aria-label="<?php _e('Replace this', 'akeebareplace') ?>" name="replace_from"><?php echo implode("\n", array_keys($replacements)) ?></textarea>
            </div>
            <div class="akeeba-form-group">
                <label for="akeebareplaceTextboxTo">
				    <?php _e('Replace with that', 'akeebareplace') ;?>
                </label>
                <textarea id="akeebareplaceTextboxTo" aria-label="<?php _e('Replace with that', 'akeebareplace') ?>" name="replace_to"><?php echo implode("\n", array_values($replacements)) ?></textarea>
            </div>

        </div>

        <div id="akeebareplaceGUIEditor"></div>

        <p></p>

        <div class="akeeba-form-group--actions">
			<button type="submit" class="akeeba-btn--big">
				<span class="akion-play"></span>
		        <?php _e('Start replacing', 'akeebabackup') ?>
			</button>

			<button type="button" onclick="akeeba.replace.showOptions('akeebareplace-replace-advanced'); return false;" class="akeeba-btn--dark">
				<span class="akion-ios-gear"></span>
		        <?php _e('Show / hide advanced options', 'akeebabackup') ?>
			</button>

			<a href="<?php echo htmlentities($this->cancelURL) ?>" class="akeeba-btn--red">
				<span class="akion-chevron-left"></span>
		        <?php _e('Go back', 'akeebareplace') ?>
			</a>

			<a href="<?php echo $this->resetURL ?>" class="akeeba-btn--orange">
				<span class="akion-android-refresh"></span>
		        <?php _e('Reset', 'akeebabackup') ?>
			</a>


		</div>
    </div>

    <div id="akeebareplace-replace-options" class="akeeba-panel--information">
        <header class="akeeba-block-header">
            <h3>
				<?php _e('Where and how to replace') ?>
            </h3>
        </header>

        <div class="akeeba-form-group--checkbox--pull-right">
            <label>
                <input type="checkbox" name="liveMode" <?php if ($this->configuration->isLiveMode()) echo 'checked="checked"' ?>>
			    <?php _e('Live mode', 'akeebareplace') ?>
                <p class="akeeba-help-text">
				    <?php _e('Apply the replacements in the database. Uncheck this, run and view the log to see what will happen without messing up your database.', 'akeebareplace') ?>
                </p>
            </label>
        </div>

        <div class="akeeba-form-group--checkbox--pull-right">
            <label>
                <input type="checkbox" name="exportAsSQL" <?php if ($this->configuration->getOutputSQLFile() != '') echo 'checked="checked"' ?>>
			    <?php _e('Export as a SQL file', 'akeebareplace') ?>
                <p class="akeeba-help-text">
				    <?php _e('Writes the database commands which apply the replacements to a SQL file before executing them in the database.', 'akeebareplace') ?>
                </p>
            </label>
        </div>

        <div class="akeeba-form-group">
            <label for="akeebareplaceExcludeTables">
	            <?php _e('Exclude these tables', 'akeebareplace') ?>
            </label>
            <?php echo Form::selectExcludeTables('excludeTables', 'akeebareplaceExcludeTables', $this->configuration->getExcludeTables(), $this->configuration->isAllTables()) ?>
            <p class="akeeba-help-text">
		        <?php _e('Select which tables should not have their data replaced. Useful for very big log tables with no replaceable data such as those created by security and e-commerce plugins. Use CTRL-click (CMD-click on macOS) to select multiple tables.', 'akeebareplace') ?>
            </p>
        </div>
    </div>

    <div id="akeebareplace-replace-advanced" class="akeeba-panel--danger" style="display: none">
        <header class="akeeba-block-header">
            <h3>
				<?php _e('Advanced options') ?>
            </h3>
        </header>

        <p>
		    <?php _e('These options are meant for expert users. Please consult the documentation for more information on the subtle but important ways they change the way Akeeba Replace works.', 'akeebareplace') ?>
        </p>


        <h4>
		    <?php _e('Behaviour', 'akeebareplace') ?>
        </h4>

        <p>
		    <?php _e('Options which modify the way replacements take place.', 'akeebareplace') ?>
        </p>

        <div class="akeeba-form-group">
            <label for="akeebareplaceBatchSize">
	            <?php _e('Maximum batch size', 'akeebareplace') ?>
            </label>
            <input name="batchSize" id="akeebareplaceBatchSize" type="number" min="0" max="10000" value="<?php echo $this->configuration->getMaxBatchSize() ?>" />
            <p class="akeeba-help-text">
	            <?php _e('The maximum number of database rows to read at once. The bigger this number the faster the replacement is but the more memory it needs to run.', 'akeebareplace') ?>
            </p>
        </div>

        <div class="akeeba-form-group">
            <label for="akeebareplaceLogLevel">
			    <?php _e('Log level', 'akeebareplace') ?>
            </label>
            <select id="akeebareplaceLogLevel" name="logLevel">
                <option value="10" <?php if ($effectiveLogLevel == 10) echo 'selected="selected"' ?>>
                    <?php _e('All information and debug', 'akeebareplace') ?>
                </option>
                <option value="20" <?php if ($effectiveLogLevel == 20) echo 'selected="selected"' ?>>
	                <?php _e('All information', 'akeebareplace') ?>
                </option>
                <option value="30" <?php if ($effectiveLogLevel == 30) echo 'selected="selected"' ?>>
	                <?php _e('Warnings and errors', 'akeebareplace') ?>
                </option>
                <option value="40" <?php if ($effectiveLogLevel == 40) echo 'selected="selected"' ?>>
	                <?php _e('Only errors', 'akeebareplace') ?>
                </option>
                <option value="50" <?php if ($effectiveLogLevel == 50) echo 'selected="selected"' ?>>
	                <?php _e('None', 'akeebareplace') ?>
                </option>
            </select>
            <p class="akeeba-help-text">
			    <?php _e('How much detail should be included in the log file.', 'akeebareplace') ?>
            </p>
        </div>

        <div class="akeeba-form-group--checkbox--pull-right">
            <label>
                <input type="checkbox" name="regularExpressions" <?php if ($this->configuration->isRegularExpressions()) echo 'checked="checked"' ?>>
				<?php _e('Replacements are given as Regular Expressions', 'akeebareplace') ?>
                <p class="akeeba-help-text">
					<?php _e('Treat the replacements in the “What to replace” area as Regular Expressions.', 'akeebareplace') ?>
                </p>
            </label>
        </div>

        <div class="akeeba-form-group--checkbox--pull-right">
            <label>
                <input type="checkbox" name="takeBackups" <?php if ($this->configuration->getBackupSQLFile() != '') echo 'checked="checked"' ?>>
				<?php _e('Take backups', 'akeebareplace') ?>
                <p class="akeeba-help-text">
					<?php _e('Create a backup of the data it replaces, before it applies the replacement.', 'akeebareplace') ?>
                </p>
            </label>
        </div>

        <h4>
            <?php _e('Advanced exclusions', 'akeebareplace') ?>
        </h4>

        <p>
		    <?php _e('Change which database tables and columns will be considered for data replacements.', 'akeebareplace') ?>
        </p>

        <div class="akeeba-form-group--checkbox--pull-right">
            <label>
                <input type="checkbox" name="allTables" id="akeebareplace-allTables" <?php if ($this->configuration->isAllTables()) echo 'checked="checked"' ?>>
				<?php _e('All tables', 'akeebareplace') ?>
                <p class="akeeba-help-text">
					<?php _e('Replace data in all database tables, even those whose name does not begin with your site\'s prefix.', 'akeebareplace') ?>
                </p>
            </label>
        </div>

        <div class="akeeba-form-group">
            <label for="akeebareplaceExcludeRows">
			    <?php _e('Exclude specific columns', 'akeebareplace') ?>
            </label>
            <textarea id="akeebareplaceExcludeRows" name="excludeRows"><?php echo $this->excludedColumns ?></textarea>
            <p class="akeeba-help-text">
		        <?php _e('Enter the name of specific table columns to exclude in the form tableName.columnName e.g. <code>wp_options.option_name</code>. Separate multiple table and column pairs with spaces, commas or new lines e.g. <code>wp_options.option_name, wp_comments.comment_author</code>.', 'akeebareplace') ?>
            </p>
        </div>

        <h4>
	        <?php _e('Collation change', 'akeebareplace') ?>
        </h4>

        <p>
		    <?php _e('Change the database and table collation (some people call it “encoding”). The collation is what helps the database understand a bunch of ones and zeroes as text. If you are using Emojis, Traditional Chinese characters or other less frequently used characters / languages you are recommended setting this to <code>utf8mb4_unicode_520_ci</code> if your database server supports it. Otherwise select <code>utf8_general_ci</code>. Only use other collations if you know what you are doing and have a specific use case which calls for it.', 'akeebareplace') ?>
        </p>


        <div class="akeeba-block--warning">
	        <?php _e('Going from a UTF8MB4 collation to UTF8 or from either of them to a non-UTF8 collation may result <strong>in permanent loss of data</strong>. This is how collations work and it can NOT be prevented. Automatic backups taken by Akeeba Replace WILL NOT help you in this case. <strong>Always take a full database backup before applying collation changes</strong>.', 'akeebareplace') ?>
        </div>

        <div class="akeeba-form-group">
            <label for="akeebareplaceDatabaseCollation">
			    <?php _e('Change the database collation', 'akeebareplace') ?>
            </label>
            <?php echo Form::selectCollation('databaseCollation', 'akeebareplaceDatabaseCollation', $this->configuration->getDatabaseCollation()) ?>
            <p class="akeeba-help-text">
		        <?php _e('Change the default database collation. This only affects tables which will be created after this change takes place. It does not modify existing tables. It may not always work, depending on your database server type, database server version and the privileges your database user has on the database server.', 'akeebareplace') ?>
            </p>
            <p class="akeeba-help-text">
            </p>
        </div>

        <div class="akeeba-form-group">
            <label for="akeebareplaceTableCollation">
			    <?php _e('Change the collation of existing tables', 'akeebareplace') ?>
            </label>
	        <?php echo Form::selectCollation('tableCollation', 'akeebareplaceTableCollation', $this->configuration->getTableCollation()) ?>
            <p class="akeeba-help-text">
		        <?php _e('Change the collation of tables already present in the database. This will not affect tables which will be created after this change takes place. It may not always work, depending on the table structure, your database server type, database server version and the privileges your database user has on the database server.', 'akeebareplace') ?>
            </p>
        </div>
    </div>
</form>

<?php
$escapedTablesUrl = addcslashes($this->tablesURL, "'\\");
$js = <<< JS
akeeba.System.documentReady(function() {
	function akeebaReplaceSetupEditor()
	{
		if ((typeof(akeeba) === 'undefined') || typeof(akeeba.replace) === 'undefined')
		{
			setTimeout(akeebaReplaceSetupEditor, 500);

			return;
		}

		akeeba.replace.strings['lblKey'] = '$lblKey';
		akeeba.replace.strings['lblValue'] = '$lblValue';
		akeeba.replace.strings['lblDelete'] = '$lblDelete';

		akeeba.replace.showEditor(document.getElementById('akeebareplaceGUIEditor'), document.getElementById('akeebareplaceTextboxEditor'));

		akeeba.replace.tablesAjaxURL = '$escapedTablesUrl';
		document.getElementById('akeebareplace-allTables').onchange = akeeba.replace.onAllTablesChange;
	}

	akeebaReplaceSetupEditor();
});

JS;

wp_add_inline_script('akeebareplace-editor', $js);
?>