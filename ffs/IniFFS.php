<?php
/**
 * Support for ini message file format.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * IniFFS currently parses and generates flat ini files with language
 * code as header key.
 *
 * @ingroup FFS
 * @since 2012-11-19
 */
class IniFFS extends SimpleFFS {
	public static function isValid( $data ) {
		$conf = array( 'BASIC' => array( 'class' => 'FileBasedMessageGroup', 'namespace' => 8 ) );
		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $conf );

		wfSuppressWarnings();
		$ffs = new IniFFS( $group );
		$parsed = $ffs->readFromVariable( $data );
		wfRestoreWarnings();

		return (bool)count( $parsed['MESSAGES'] );
	}

	public function supportsFuzzy() {
		return 'write';
	}

	public function getFileExtensions() {
		return array( '.ini' );
	}

	/**
	 * @param string $data
	 * @return array Parsed data.
	 */
	public function readFromVariable( $data ) {
		$authors = array();
		preg_match_all( '/^; Author: (.*)$/m', $data, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			$authors[] = $match[1];
		}

		// Remove comments
		$data = preg_replace( '/^\s*;.*$/m', '', $data );
		// Make sure values are quoted, PHP barks on stuff like ?{}|&~![()^
		$data = preg_replace( '/(^.+?=\s*)([^\'"].+)$/m', '\1"\2"', $data );

		$messages = parse_ini_string( $data );
		if ( is_array( $messages ) ) {
			$messages = $this->group->getMangler()->mangle( $messages );
		} else {
			$messages = null;
		}

		return array(
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
		);
	}

	protected function writeReal( MessageCollection $collection ) {
		$output = '';
		$mangler = $this->group->getMangler();

		/**
		 * @var $m ThinMessage
		 */
		foreach ( $collection as $key => $m ) {
			$value = $m->translation();
			if ( $value === null ) {
				continue;
			}

			$comment = '';

			if ( $m->hasTag( 'fuzzy' ) ) {
				$value = str_replace( TRANSLATE_FUZZY, '', $value );
				$comment = "; Fuzzy\n";
			}

			$key = $mangler->unmangle( $key );
			$output .= "$comment$key = $value\n";
		}

		// Do not create empty files
		if ( $output === '' ) {
			return '';
		}

		global $wgSitename;
		// Accumulator
		$header = "; Exported from $wgSitename\n";

		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->getLanguage() );
		foreach ( $authors as $author ) {
			$header .= "; Author: $author\n";
		}

		$header .= '[' . $collection->getLanguage() . "]\n";

		return $header . $output;
	}
}
