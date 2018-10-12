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
	<span class="aklogo-akeebareplace-wp-small"></span>
	Akeeba Replace
	<?php if ($hasSubheading): ?>
	<small>:: <?php echo $subheading?></small>
	<?php endif; ?>
</h2>