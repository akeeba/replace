<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

$hasSubheading = isset($subheading);
?>
<h2 class="akeebareplace-title<?php echo $hasSubheading ? '-inline' : '' ?>">
	<img src="<?php echo plugins_url('images/logo/akeeba-replace-128-black.png', AKEEBA_REPLACE_SELF) ?>"
    class="akeebareplace-header-logo">
	Akeeba Replace
	<?php if ($hasSubheading): ?>
	<small>:: <?php echo $subheading?></small>
	<?php endif; ?>
</h2>