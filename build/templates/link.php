<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

$hardlink_files = [
];

$symlink_files = [
];

$symlink_folders = [
	'src/lib'           => 'src/akeebareplace/includes/lib',

	# Akeeba FEF
	'../fef/out/css'    => 'src/akeebareplace/fef/css',
	'../fef/out/fonts'  => 'src/akeebareplace/fef/fonts',
	'../fef/out/images' => 'src/akeebareplace/fef/images',
	'../fef/out/js'     => 'src/akeebareplace/fef/js',
];
