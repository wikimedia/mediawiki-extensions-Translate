<?php
/**
 * Job for updating translation pages.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Job for updating translation pages when translation or message definition changes.
 *
 * @ingroup JobQueue
 */
class MessageUpdateJob extends Job {
	public static function newJob( Title $target, $content, $fuzzy = false ) {
		$params = [
			'content' => $content,
			'fuzzy' => $fuzzy,
		];
		$job = new self( $target, $params );

		return $job;
	}

	/**
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params = [] ) {
		parent::__construct( __CLASS__, $title, $params );
	}

	public function run() {
		global $wgTranslateDocumentationLanguageCode;

		$title = $this->title;
		$params = $this->params;
		$user = FuzzyBot::getUser();
		$flags = EDIT_FORCE_BOT;

		$wikiPage = WikiPage::factory( $title );
		$summary = wfMessage( 'translate-manage-import-summary' )
			->inContentLanguage()->plain();
		$content = ContentHandler::makeContent( $params['content'], $title );
		$wikiPage->doEditContent( $content, $summary, $flags, false, $user );

		// NOTE: message documentation is excluded from fuzzying!
		if ( $params['fuzzy'] ) {
			$handle = new MessageHandle( $title );
			$key = $handle->getKey();

			$languages = TranslateUtils::getLanguageNames( 'en' );
			unset( $languages[$wgTranslateDocumentationLanguageCode] );
			$languages = array_keys( $languages );

			$dbw = wfGetDB( DB_MASTER );
			$fields = [ 'page_id', 'page_latest' ];
			$conds = [ 'page_namespace' => $title->getNamespace() ];

			$pages = [];
			foreach ( $languages as $code ) {
				$otherTitle = Title::makeTitleSafe( $title->getNamespace(), "$key/$code" );
				$pages[$otherTitle->getDBkey()] = true;
			}
			unset( $pages[$title->getDBkey()] );
			if ( $pages === [] ) {
				return true;
			}

			$conds['page_title'] = array_keys( $pages );

			$res = $dbw->select( 'page', $fields, $conds, __METHOD__ );
			$inserts = [];
			foreach ( $res as $row ) {
				$inserts[] = [
					'rt_type' => RevTag::getType( 'fuzzy' ),
					'rt_page' => $row->page_id,
					'rt_revision' => $row->page_latest,
				];
			}

			if ( $inserts === [] ) {
				return true;
			}

			$dbw->replace(
				'revtag',
				[ [ 'rt_type', 'rt_page', 'rt_revision' ] ],
				$inserts,
				__METHOD__
			);
		}

		return true;
	}
}
