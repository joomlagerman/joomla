<?php
/**
 * @package    Joomla.Language
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @copyright  (C) Translation 2008 - 2026 J!German <https://www.jgerman.de>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * de-DE localise class.
 *
 * @since  1.6
 */
abstract class De_DELocalise
{
	/**
	 * Returns the potential suffixes for a specific number of items
	 *
	 * @param int $count  The number of items.
	 *
	 * @return  array  An array of potential suffixes.
	 *
	 * @since   1.6
	 */
	public static function getPluralSuffixes($count)
	{
		if ($count == 0) {
			return ['0'];
		} elseif ($count == 1) {
			return ['ONE', '1'];
		} else {
			return ['OTHER', 'MORE'];
		}
	}
}
