<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

use Akeeba\Replace\WordPress\Helper\Application;

/** @var \Akeeba\Replace\WordPress\View\Replace\Html $this */

wp_enqueue_style('akeebareplace-backend', plugins_url('/css/backend.css', AKEEBA_REPLACE_SELF), ['fef'], Application::getMediaVersion());
?>

<?php echo $this->getRenderedTemplate('Common', 'header'); ?>
<?php echo $this->getRenderedTemplate('Common', 'errorDialog'); ?>
<?php echo $this->getRenderedTemplate('', 'initial_confirmation'); ?>
<?php echo $this->getRenderedTemplate('', 'progress'); ?>
<?php echo $this->getRenderedTemplate('', 'complete'); ?>
<?php echo $this->getRenderedTemplate('', 'warnings'); ?>
<?php echo $this->getRenderedTemplate('', 'retry'); ?>
<?php echo $this->getRenderedTemplate('', 'error'); ?>