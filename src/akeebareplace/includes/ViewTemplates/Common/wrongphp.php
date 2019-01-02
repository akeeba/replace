<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

use Akeeba\Replace\WordPress\Helper\Application;

?>
<div class="wrap">
	<h2>
		<?php echo sprintf(_('Outdated PHP version %s detected', 'akeebareplace'), PHP_VERSION); ?>
	</h2>

	<hr/>

	<div id="message" class="error">
		<p>
			<?php echo sprintf(_('Akeeba Replace requires PHP %s or any later version to work', 'akeebareplace'), Application::MINIMUM_PHP_VERSION); ?>
		</p>
	</div>

	<p>
		<?php echo sprintf(_('We <b>strongly</b> urge you to update to PHP %s or later. If unsure how to do this, please ask your host.', 'akeebareplace'), Application::RECOMMENDED_PHP_VERSION) ?>
	</p>
	<p>
		<a href="https://www.akeebabackup.com/how-do-version-numbers-work.html">
			<?php _e('Version numbers don\'t make sense?', 'akeebareplace'); ?>
		</a>
	</p>

	<hr/>

	<h3>
		<?php _e('Security advice'); ?>
	</h3>
	<p>
		<?php echo sprintf(_('The version of PHP you are currently using, %s, has reached the end of its life.', 'akeebareplace'), PHP_VERSION) ?>
		<?php _e('End-of-life versions of PHP contain bugs and security issues which may lead to your site becoming compromised (hacked). These issues are fixed in newer versions of PHP.', 'akeebareplace'); ?>
		<?php _e('Please refer to the <a href="http://php.net/eol.php">official PHP end-of-life page</a> for more information.', 'akeebareplace')?>
	</p>

	<h3>
		<?php _e('Have you upgraded PHP and still see this message?', 'akeebareplace')?>
	</h3>
	<p>
		<?php _e('Akeeba Replace displays this page based on the version that PHP itself reports. Simply put, it cannot detect the wrong version. You can, however, end up using the wrong PHP version instead of the one you intended. If you have replaced or removed your .htaccess file your PHP version upgrade may have been undone. On some servers you have to tell it to apply the new PHP version on each folder separately. Please ask your host for further information.', 'akeebareplace') ?>
	</p>
</div>
