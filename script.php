<?php
/**
 * @package    Joomla.Language
 *
 * @copyright  (C) 2021 J!German <https://www.jgerman.de>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Installer\InstallerScript;

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since  4.0.0v1
 */
class Pkg_deDEInstallerScript extends InstallerScript
{
	/**
	 * Extension script constructor.
	 *
	 * @since   4.0.0v1
	 */
	public function __construct()
	{
		// Define the minumum versions to be supported.
		$this->minimumJoomla = '4.0';
		$this->minimumPhp    = '7.2.5';

		$this->deleteFiles = array(
            '',
		);
	}

	/**
	 * Function to perform changes during postflight
	 *
	 * @param   string            $type    The action being performed
	 * @param   ComponentAdapter  $parent  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   4.0.0v1
	 */
	public function postflight($type, $parent)
	{
		$this->removeFiles();
	}
}