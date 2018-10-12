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

/** @var \Akeeba\Replace\WordPress\View\Job\Html $this */

wp_enqueue_style('akeebareplace-backend', plugins_url('/css/backend.css', AKEEBA_REPLACE_SELF), ['fef'], Application::getMediaVersion());

$subheading = __('Jobs', 'akeebareplace');

function akeebaRepalceJobDefault_renderHeaderFooter($that)
{
	?>
	<div class="alignleft actions bulkactions">
		<select name="task" id="bulk-action-selector-top">
			<option value="-1"><?php _e('Bulk Actions') ?></option>
			<option value="delete"><?php _e('Delete', 'akeebareplace') ?></option>
			<option value="deleteFiles"><?php _e('Delete Files', 'akeebareplace') ?></option>
		</select>
		<button type="submit" id="doaction" class="akeeba-btn--teal">
			<?php _e('Apply') ?>
		</button>
	</div>

	<?php echo Form::pagination($that->total, $that->limitStart) ?>
	<?php
}

?>

<?php echo $this->getRenderedTemplate('Common', 'header', '', ['subheading' => $subheading]); ?>

<span class="akeebareplace-inline-header-buttons">
	<a class="akeeba-btn--green"
	   href="<?php echo esc_url(admin_url('admin.php?page=akeebareplace&view=Replace&task=new')) ?>">
		<?php _e('Add New') ?>
	</a>
</span>

<form method="post" action="<?php echo admin_url('admin.php') ?>">
	<input type="hidden" name="page" value="akeebareplace"/>
	<input type="hidden" name="view" value="Job"/>
	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="_wpnonce" value=""/>


	<p class="search-box">
		<input type="search" name="description" value="<?php echo $this->escape($this->filters['description']) ?>"
			   placeholder="<?php _e('Description', 'akeebareplace') ?>"/>
		<button type="submit" id="search-submit" class="akeeba-btn">
			<span class="akion-ios-search"></span>
			<?php _ex('Search', 'Verb, displayed on a button', 'akeebareplace') ?>
		</button>
	</p>

	<div class="tablenav top">
		<?php akeebaRepalceJobDefault_renderHeaderFooter($this) ?>
	</div>

	<table class="akeeba-table--striped">
		<thead>
		<tr>
			<th id="cb" class="manage-column column-cb check-column" style="width:40px;">
				<input id="cb-select-all-1" type="checkbox"/>
			</th>
			<th><?php _e('ID', 'akeebarepalce') ?></th>
			<th><?php _e('Description', 'akeebarepalce') ?></th>
			<th><?php _e('Created On', 'akeebarepalce') ?></th>
			<th><?php _e('Last Run', 'akeebarepalce') ?></th>
			<th><?php _e('Actions', 'akeebarepalce') ?></th>
		</tr>
		</thead>

		<tbody>
		<?php if (count($this->items)): ?>
			<?php foreach ($this->items as $item): ?>
			<tr>
				<td class="check-column">
					<input id="cb-select-<?php echo $item->id ?>" type="checkbox" name="cb[]" value="<?php echo $item->id?>" />
				</td>
				<td><?php echo (int)$item->id ?></td>
				<td><?php echo $this->escape($item->description) ?></td>
				<td><?php echo $this->escape(Form::formatDate($item->created_on)) ?></td>
				<td><?php echo $this->escape(Form::formatDate($item->run_on)) ?></td>
				<td>
					<!-- TODO: Per-item actions -->
				</td>
			</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="20">
					<?php _e('You have not run any replacement jobs yet. Click the Add New button at the top of the page to start your first replacement.', 'akeebareplace') ?>
				</td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>

	<div class="tablenav bottom">
		<?php akeebaRepalceJobDefault_renderHeaderFooter($this) ?>
	</div>

</form>
