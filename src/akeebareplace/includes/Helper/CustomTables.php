<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\WordPress\Helper;

/**
 * A class to handle the installation and removal of custom tables
 * 
 * @package Akeeba\Replace\WordPress\Helper
 */
class CustomTables
{
	/**
	 * The current version of the database schema
	 *
	 * @var  string
	 */
	private $dbVersion = '1.0';

	/**
	 * Create queries for all custom tables
	 *
	 * @var  array
	 */
	private $createQueries = [
		'CREATE TABLE `#__akeebareplace_jobs`
(
  `id` BIGINT AUTO_INCREMENT,
  `description` VARCHAR(200) NOT NULL,
  `options` MEDIUMTEXT NOT NULL,
  `created_on` DATETIME NOT NULL DEFAULT \'0000-00-00\',
  `run_on` DATETIME NOT NULL DEFAULT \'0000-00-00\',
  PRIMARY KEY (`id`)
) ###COLLATION###;',
	];

	/**
	 * Drop queries for all custom tables
	 *
	 * @var  array
	 */
	private $dropQueries = [
		'DROP TABLE IF EXISTS `#__akeebareplace_jobs`',
	];

	/**
	 * Install or upgrade the database tables and data
	 */
	public function install($force = false)
	{
		// Get the current version of the database
		$lastVersion = get_option('akeebareplace_db_version', '0.0');

		// Already updated? Nothing to do, then!
		if (!$force && ($lastVersion == $this->dbVersion))
		{
			return;
		}

		// Install tables and data
		$this->installTables($lastVersion);
		$this->installData($lastVersion);

		// Store the current schema version
		if ($lastVersion == '0.0')
		{
			add_option( 'akeebareplace_db_version', $this->dbVersion);

			return;
		}

		update_option('akeebareplace_db_version', $this->dbVersion);
	}

	public function uninstall()
	{
		global $wpdb;

		array_walk($this->dropQueries, function ($sql) use ($wpdb) {
			$sql = str_replace('#__', $wpdb->prefix, $sql);
			$wpdb->query($sql);
		});

		delete_option('akeebareplace_db_version');
	}

	/**
	 * Install the custom tables
	 *
	 * @param   string  $lastInstalledVersion  The schema version installed before we were called
	 *
	 * @return  void
	 */
	private function installTables($lastInstalledVersion)
	{
		global $wpdb;

		$replacements = [
			'#__'             => $wpdb->prefix,
			'###COLLATION###' => $wpdb->get_charset_collate(),
		];

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$sql = array_map(function ($query) use ($replacements) {
			return str_replace(array_keys($replacements), array_values($replacements), $query);
		}, $this->createQueries);

		dbDelta($sql);
	}

	/**
	 * Install data to the custom tables
	 *
	 * @param   string  $lastInstalledVersion  The schema version installed before we were called
	 *
	 * @return  void
	 */
	private function installData($lastInstalledVersion)
	{
		// We don't have any data to install. Yet.
	}
}