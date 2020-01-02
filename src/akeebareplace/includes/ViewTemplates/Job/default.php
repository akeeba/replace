<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

use Akeeba\Replace\WordPress\Helper\Application;
use Akeeba\Replace\WordPress\Helper\Form;
use Akeeba\Replace\WordPress\Helper\WordPress;

/** @var \Akeeba\Replace\WordPress\View\Job\Html $this */

wp_enqueue_script('akeebareplace-jobs', plugins_url('/js/jobs.js', AKEEBA_REPLACE_SELF), ['akeebareplace-system'], Application::getMediaVersion());

$subheading = __('Manage', 'akeebareplace');

function akeebaReplaceJobDefault_renderHeaderFooter($that, $topBottom = 'top')
{
	?>
	<div class="alignleft actions bulkactions">
		<select id="bulk-action-selector-<?php echo $topBottom ?>">
			<option value="-1"><?php _e('Bulk Actions') ?></option>
			<option value="delete"><?php _e('Delete', 'akeebareplace') ?></option>
			<option value="deleteFiles"><?php _e('Delete Files', 'akeebareplace') ?></option>
		</select>
		<button type="submit" id="doaction" class="akeeba-btn--teal">
			<?php _e('Apply') ?>
		</button>
	</div>

	<?php echo Form::pagination($that->total, $that->limitStart, null, $topBottom) ?>
	<?php
}

?>

<?php echo $this->getRenderedTemplate('Common', 'header', '', ['subheading' => $subheading]); ?>

<?php echo $this->getRenderedTemplate('Common', 'phpversion_warning', '', [
	'minPHPVersion'         => Application::MINIMUM_PHP_VERSION,
	'recommendedPHPVersion' => Application::RECOMMENDED_PHP_VERSION,
	'softwareName'          => 'Akeeba Replace',
]); ?>

<span class="akeebareplace-inline-header-buttons">
	<a class="akeeba-btn--green"
	   href="<?php echo esc_url(WordPress::adminUrl('admin.php?page=akeebareplace&view=Replace&reset=1')) ?>">
		<?php _e('Add New') ?>
	</a>
</span>

<form method="post">
	<input type="hidden" name="page" value="akeebareplace"/>
	<input type="hidden" name="view" value="Job"/>
	<input type="hidden" name="task" value="browse" id="akeebareplace-task"/>
	<input type="hidden" name="_wpnonce" id="akeebareplace-nonce" value=""/>


	<p class="search-box">
		<input type="search" name="description" value="<?php echo $this->escape($this->filters['description']) ?>"
			   placeholder="<?php _e('Description', 'akeebareplace') ?>"/>
		<button type="submit" id="search-submit" class="akeeba-btn">
			<span class="akion-ios-search"></span>
			<?php _ex('Search', 'Verb, displayed on a button', 'akeebareplace') ?>
		</button>
	</p>

	<div class="tablenav top">
		<?php akeebaReplaceJobDefault_renderHeaderFooter($this, 'top') ?>
	</div>

	<table class="akeeba-table--striped">
		<thead>
		<tr>
			<th id="cb" class="manage-column column-cb check-column" style="width:40px;">
				<input id="cb-select-all-1" type="checkbox"/>
			</th>
			<th><?php _e('ID', 'akeebarepalce') ?></th>
			<th><?php _e('Description', 'akeebarepalce') ?></th>
			<th><?php _ex('Run On', 'Shorthand for "this job was last run on the date printed in this column"', 'akeebarepalce') ?></th>
		</tr>
		</thead>

		<tbody>
		<?php if (count($this->items)): ?>
			<?php
			foreach ($this->items as $item):
			$recordFiles = $this->getModel()->getFilePathsForRecord($item);
			$hasFiles = !empty($recordFiles['log']) || !empty($recordFiles['output']) || !empty($recordFiles['backup']);
			?>
			<tr>
				<td class="check-column">
					<input id="cb-select-<?php echo $item->id ?>" type="checkbox" name="cb[]" value="<?php echo $item->id?>" />
				</td>
				<td><?php echo (int)$item->id ?></td>
				<td>
					<strong><?php echo $this->escape($item->description) ?></strong>
					<div class="row-actions">
						<span>
							<a href="<?php echo WordPress::adminUrl('admin.php?page=akeebareplace&view=Replace&id=' . $item->id) ?>">
								<?php _e('Clone', 'akeebareplace') ?>
							</a>
						</span>
						<?php if ($hasFiles): ?>
						|
						<span class="trash">
							<a href="<?php echo wp_nonce_url(WordPress::adminUrl('admin.php?page=akeebareplace&view=Job&task=deleteFiles&id=' . $item->id), 'get_Job_deleteFiles') ?>" class="submitdelete">
								<?php _e('Delete Files', 'akeebareplace') ?>
							</a>
						</span>
						<?php endif; ?>
						|
						<span class="trash">
							<a href="<?php echo wp_nonce_url(WordPress::adminUrl('admin.php?page=akeebareplace&view=Job&task=delete&id=' . $item->id), 'get_Job_delete') ?>" class="submitdelete">
								<?php _e('Delete', 'akeebareplace') ?>
							</a>
						</span>
						<?php if (!empty($recordFiles['log'])): ?>
							|
							<a href="<?php echo WordPress::adminUrl('admin.php?page=akeebareplace&view=Log&id=' . $item->id) ?>">
								<?php _e('View log' ,'akeebareplace') ?>
							</a>
						<?php endif; ?>
						<?php if (!empty($recordFiles['output'])): ?>
							|
							<a href="<?php echo WordPress::adminUrl('admin.php?page=akeebareplace&view=Job&task=downloadOutput&id=' . $item->id) ?>">
								<?php _e('Download output SQL' ,'akeebareplace') ?>
							</a>
						<?php endif; ?>
						<?php if (!empty($recordFiles['backup'])): ?>
							|
							<a href="<?php echo WordPress::adminUrl('admin.php?page=akeebareplace&view=Job&task=downloadBackup&id=' . $item->id) ?>">
								<?php _e('Download backup' ,'akeebareplace') ?>
							</a>
							<!--
							|
							<a href="<?php echo WordPress::adminUrl('admin.php?page=akeebareplace&view=Restore&id=' . $item->id) ?>">
								<?php _e('Restore backup' ,'akeebareplace') ?>
							</a>
							-->
						<?php endif; ?>

					</div>
				</td>
				<td><?php echo $this->escape(Form::formatDate($item->run_on)) ?></td>
			</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="20">
					<?php if (empty($this->filters['description'])): ?>
						<?php _e('You have not run any replacement jobs yet. Click the Add New button at the top of the page to start your first replacement.', 'akeebareplace') ?>
					<?php else: ?>
						<?php _e('Nothing matches the filters you specified.', 'akeebareplace') ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>

	<div class="tablenav bottom">
		<?php akeebaReplaceJobDefault_renderHeaderFooter($this, 'bottom') ?>
	</div>

</form>

<?php
$nonceDeleteFiles = wp_create_nonce('post_Job_deleteFiles');
$nonceDelete      = wp_create_nonce('post_Job_delete');

$js = <<< JS
akeeba.System.documentReady(function() {
	akeeba.replace.nonces['delete'] = '{$nonceDelete}';
	akeeba.replace.nonces['deleteFiles'] = '{$nonceDeleteFiles}';
});

JS;

wp_add_inline_script('akeebareplace-jobs', $js);
?>