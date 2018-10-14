<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

/** @var \Akeeba\Replace\WordPress\View\Log\Html $this */

$subheading = __('View Log', 'akeebareplace');

?>
<?php echo $this->getRenderedTemplate('Common', 'header', '', ['subheading' => $subheading]); ?>

<div style="text-align: right">
	<a class="akeeba-btn--big"
	   href="<?php echo admin_url('admin.php?page=akeebareplace&view=Job&task=downloadLog&id=' . $this->logId) ?>">
		<span class="akion-ios-download"></span>
		<?php _e('Download Log File', 'akeebareplace') ?>
	</a>
</div>

<div class="akeeba-panel--information" id="akeebareplace-frame-holder">
	<?php if ($this->logTooBig): ?>
		<p class="alert alert-info">
			<?php echo sprintf(__("Your log file is %s Mb big. Trying to display it in the browser may crash the browser or cause a timeout error on your server. Please use the Download Log button above to download the log file to your computer instead. You can open and read the log with any plain text editor.", 'akeebareplace'), number_format($this->logSize / (1024 * 1024), 2)) ?>
		</p>
		<span class="akeeba-btn--orange" id="akeebareplace-showlog">
			<span class="akion-ios-eye"></span>
			<?php _e('Display log anyway', 'akeebareplace') ?>
        </span>
		<?php
		$iFrameSrc = addcslashes(admin_url('admin.php?page=akeebareplace&view=Log&task=dump&id=' . $this->logId), "\\'");
		$js = <<< JS
akeeba.System.documentReady(function() {
	akeeba.System.addEventListener(document.getElementById('akeebareplace-showlog'), 'click', function () {
		var elHolder = document.getElementById('akeebareplace-frame-holder');
		var elFrame = document.createElement('iframe');
		elFrame.src = '$iFrameSrc';
		elFrame.width = '99%';
		elFrame.height = '500px';
		elHolder.innerHTML = '';
		elHolder.appendChild(elFrame);
	});
	
});

JS;

		wp_add_inline_script('akeebareplace-system', $js);

		?>
	<?php else: ?>
		<iframe
				src="<?php echo admin_url('admin.php?page=akeebareplace&view=Log&task=dump&id=' . $this->logId) ?>"
				width="99%" height="500px">
		</iframe>
	<?php endif; ?>
</div>
