<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

/** @var \Akeeba\Replace\WordPress\View\ControlPanel\Html $this */
?>

<?php echo $this->getRenderedTemplate('Common', 'header'); ?>

<?php echo $this->getRenderedTemplate(null, 'welcome'); ?>

<?php if (!defined('DISABLE_NAG_NOTICES') || !DISABLE_NAG_NOTICES) {
	echo $this->getRenderedTemplate(null, 'nag');
} ?>

<?php echo $this->getRenderedTemplate(null, 'form'); ?>
