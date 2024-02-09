<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

$hardlink_files = [
];

$symlink_files = [
];

$symlink_folders = [
	'src/lib'           => 'src/akeebareplace/includes/lib',

	# Akeeba FEF
	'../fef-1.x/out/css'    => 'src/akeebareplace/fef/css',
	'../fef-1.x/out/fonts'  => 'src/akeebareplace/fef/fonts',
	'../fef-1.x/out/images' => 'src/akeebareplace/fef/images',
	'../fef-1.x/out/js'     => 'src/akeebareplace/fef/js',
];
