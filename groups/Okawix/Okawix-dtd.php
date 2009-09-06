<?php

 /**
 * @copyright Copyright © 2009, Guillaume Duhamel
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class OkawixDtdFFS extends SimpleFFS {
	public function readFromVariable( $data ) {
		preg_match_all( ',AUTHOR: ([^\n]+)\n,', $data, $matches );
		$authors = array();
		for($i = 0;$i < count($matches[1]);$i++) {
			$authors[] = $matches[1][$i];
		}

		preg_match_all( ',<!ENTITY[ ]+([^ ]+)[ ]+"([^"]+)"[^>]*>,', $data, $matches );

		$keys = $matches[1];
		$values = $matches[2];

		$messages = array();
		for($i = 0;$i < count($matches[1]);$i++) {
			$messages[$keys[$i]] = str_replace(
				array('&quot;', '&#34;', '&#39;'),
				array('"', '"', "'"),
				$values[$i]);
		}

		$messages = $this->group->getMangler()->mangle( $messages );

		return array(
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		);
	}

	protected function writeReal( MessageCollection $collection ) {
		global $wgSitename;

		$collection->loadTranslations();

		$header = "<!--\n";
		$header .= "COMMENT: Exported from $wgSitename\n\n";

		$authors = $collection->getAuthors();
		if (count($authors) > 0) {

			foreach ( $authors as $author ) {
				$header .= "AUTHOR: $author\n";
			}
		}
		$header .= "-->\n";

		$output = '';
		$mangler = $this->group->getMangler();
		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );
			$trans = $m->translation();
			$trans = str_replace( TRANSLATE_FUZZY, '', $trans );
			if ( $trans === '' ) continue;

			$trans = str_replace('"', '&quot;', $trans);
			$output .= "<!ENTITY $key \"$trans\">\n";
		}
		return $output ? $header.$output : false;
	}
}
