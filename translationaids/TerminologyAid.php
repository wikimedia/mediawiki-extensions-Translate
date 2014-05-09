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
				$terms[] = array(
					'term' => $text,
					'range' => array( $pos, $pos + strlen( $text ) ),
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
