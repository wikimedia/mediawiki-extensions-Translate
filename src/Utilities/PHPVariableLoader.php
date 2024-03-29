<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

/**
 * Stuff for handling configuration files in PHP format.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2010 Niklas Laxström
 * @license GPL-2.0-or-later
 */

class PHPVariableLoader {
	/**
	 * Returns a global variable from PHP file by executing the file.
	 * @param string $_filename Path to the file.
	 * @param string $_variable Name of the variable.
	 * @return mixed|null The variable contents or null.
	 */
	public static function loadVariableFromPHPFile( string $_filename, string $_variable ) {
		if ( !file_exists( $_filename ) ) {
			return null;
		} else {
			require $_filename;

			return $$_variable ?? null;
		}
	}
}
