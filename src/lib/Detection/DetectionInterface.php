<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

namespace Akeeba\Replace\Detection;

/**
 * The interface to a script / CMS detection and configuration loading class
 */
interface DetectionInterface
{
	/**
	 * Creates a new oracle objects
	 *
	 * @param   string  $path  The directory path to scan
	 */
	public function __construct($path);

	/**
	 * Does this class recognises the script / CMS type?
	 *
	 * @return  boolean
	 */
	public function isRecognised();

	/**
	 * Return the name of the CMS / script
	 *
	 * @return  string
	 */
	public function getName();

	/**
	 * Return the database connection information for this CMS / script
	 *
	 * @return  array
	 */
	public function getDbInformation();

    /**
     * Return extra databases required by the CMS / script (ie Drupal multi-site)
     *
     * @return  array
     */
    public function getExtraDb();
}
