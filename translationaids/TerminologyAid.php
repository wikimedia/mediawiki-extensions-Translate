<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * XXX
 *
 * @ingroup TranslationAids
 * @since 2013-XX
 */
class TerminologyAid extends TranslationAid {
	public function getData() {

		$language = $this->handle->getCode();

		$db = wfGetDB( DB_MASTER );

		$conds = array(
			'page_namespace' => 1196,
		);

		$res = $db->select( 'page', 'page_title', $conds, __METHOD__);
		$pagenames = array();
		foreach ( $res as $row ) {
			$pagenames[] = $row->page_title;
		}

		$contents = TranslateUtils::getContents( $pagenames, 1196 );
		$definition = $this->getDefinition();
		$terms = array();

		foreach ( $contents as $arr ) {
			list( $text, $author ) = $arr;
			if ( strpos( $definition, $text ) !== false ) {
				$pos = strpos( $definition, $text );

				// Get translation
				$translation = $contents[$text.'/'.$language][0];
				if( is_null( $translation ) ) $translation = $text;

				$terms[] = array(
					'term' => $text,
					'range' => array( $pos, $pos + strlen( $text ) ),
					'translation' => $translation
				);
			}
		}

		if ( $terms === array() ) {
			throw new TranslationHelperException( "No terms" );
		}

		$terms['**'] = 'term';
		return $terms;
	}
}
