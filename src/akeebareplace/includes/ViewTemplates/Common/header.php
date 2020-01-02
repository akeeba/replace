<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

use Akeeba\Replace\WordPress\Helper\Application;

$hasSubheading = isset($subheading);
wp_enqueue_style('akeebareplace-backend', plugins_url('/css/backend.css', AKEEBA_REPLACE_SELF), ['fef'], Application::getMediaVersion());

?>
<h2 class="akeebareplace-title<?php echo $hasSubheading ? '-inline' : '' ?>">
	<img src="<?php echo plugins_url('images/logo/akeeba-replace-128-black.png', AKEEBA_REPLACE_SELF) ?>"
    class="akeebareplace-header-logo">
	Akeeba Replace
	<?php if ($hasSubheading): ?>
	<small>:: <?php echo $subheading?></small>
	<?php endif; ?>
</h2>