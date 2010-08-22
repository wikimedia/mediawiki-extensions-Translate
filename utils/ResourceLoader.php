<?php
/**
 * @todo Needs documentation.
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * @todo Needs documentation.
 */
class ResourceLoader {
	public static function loadVariableFromPHPFile( $_filename, $_variable ) {
		if ( !file_exists( $_filename ) ) {
			return null;
		} else {
			require( $_filename );

			return isset( $$_variable ) ? $$_variable : null;
		}
	}
}
