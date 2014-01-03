<?php
/**
 * Implements FFS for DTD file format.
 *
 * @file
 * @author Guillaume Duhamel
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2009-2010, Guillaume Duhamel, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * File format support for DTD.
 *
 * @ingroup FFS
 */
class DtdFFS extends SimpleFFS {
	public function getFileExtensions() {
		return array( '.dtd' );
	}

	/**
	 * @param $data string
	 * @return array Parsed data.
	 */
	public function readFromVariable( $data ) {
		preg_match_all( ',# Author: ([^\n]+)\n,', $data, $matches );
		$authors = array();

		$count = count( $matches[1] );
		for ( $i = 0; $i < $count; $i++ ) {
			$authors[] = $matches[1][$i];
		}

		preg_match_all( ',<!ENTITY[ ]+([^ ]+)\s+"([^"]+)"[^>]*>,', $data, $matches );

		$keys = $matches[1];
		$values = $matches[2];

		$messages = array();

		$count = count( $matches[1] );
		for ( $i = 0; $i < $count; $i++ ) {
			$messages[$keys[$i]] = str_replace(
				array( '&quot;', '&#34;', '&#39;' ),
				array( '"', '"', "'" ),
				$values[$i] );
		}

		$messages = $this->group->getMangler()->mangle( $messages );

		return array(
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		);
	}

	protected function writeReal( MessageCollection $collection ) {
		$collection->loadTranslations();

		$header = "<!--\n";
		$header .= $this->doHeader( $collection );
		$header .= $this->doAuthors( $collection );
		$header .= "-->\n";

		$output = '';
		$mangler = $this->group->getMangler();

		/**
		 * @var TMessage $m
		 */
		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );
			$trans = $m->translation();
			$trans = str_replace( TRANSLATE_FUZZY, '', $trans );

			if ( $trans === '' ) {
				continue;
			}

			$trans = str_replace( '"', '&quot;', $trans );
			$output .= "<!ENTITY $key \"$trans\">\n";
		}

		return $output ? $header . $output : false;
	}

	protected function doHeader( MessageCollection $collection ) {
		global $wgSitename;

		$code = $collection->code;
		$name = TranslateUtils::getLanguageName( $code );
		$native = TranslateUtils::getLanguageName( $code, $code );

		$output = "# Messages for $name ($native)\n";
		$output .= "# Exported from $wgSitename\n\n";

		return $output;
	}

	protected function doAuthors( MessageCollection $collection ) {
		$output = '';
		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		foreach ( $authors as $author ) {
			$output .= "# Author: $author\n";
		}

		return $output;
	}
}
