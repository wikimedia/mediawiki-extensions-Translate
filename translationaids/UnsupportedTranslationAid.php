<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Harry Burt
 * @copyright Copyright © 2013, Harry Burt
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Dummy translation aid that always errors
 *
 * @ingroup TranslationAids
 * @since 2013-03-29
 */
class UnsupportedTranslationAid extends TranslationAid {
	public function getData() {
		throw new TranslationHelperException( 'This translation aid is disabled' );
	}
}
