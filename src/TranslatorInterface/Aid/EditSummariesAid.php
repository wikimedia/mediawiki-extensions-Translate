<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use Linker;
use MediaWiki\MediaWikiServices;
use MWTimestamp;

/**
 * Translation aid that provides last X edit summaries for a translation
 *
 * @author Eugene Wang'ombe
 * @license GPL-2.0-or-later
 * @since 2022.04
 */
class EditSummariesAid extends TranslationAid {
	private const COMMENT_COUNT = 5;

	public function getData(): array {
		$pageTitle = $this->handle->getTitle();
		if ( !$pageTitle->exists() ) {
			return [];
		}

		$mwService = MediaWikiServices::getInstance();
		$revisionFactory = $mwService->getRevisionFactory();

		// Build the query to fetch the last x revisions
		$dbr = $mwService->getDBLoadBalancer()->getConnectionRef( DB_REPLICA );
		$options = [ 'ORDER BY' => 'rev_timestamp DESC, rev_id DESC' ];
		$options[ 'LIMIT' ] = self::COMMENT_COUNT;
		$aid = $pageTitle->getArticleID();
		$revQuery = $revisionFactory->getQueryInfo();
		$result = $dbr->select(
			$revQuery[ 'tables' ],
			$revQuery[ 'fields' ],
			[ 'rev_page' => $aid ],
			__METHOD__,
			$options,
			$revQuery[ 'joins' ]
		);

		$editSummaries = [];
		$commentFormatter = method_exists( $mwService, 'getCommentFormatter' )
			? $mwService->getCommentFormatter() : null;
		foreach ( $result as $row ) {
			$revision = $revisionFactory->newRevisionFromRow( $row );
			$comment = $revision->getComment();

			// The result of getComment() may return null. In that case
			// skip processing of the summary.
			if ( !$comment ) {
				continue;
			}

			if ( $commentFormatter ) {
				$message = $commentFormatter->format( $comment->message->text() );
			} else {
				// <= MW 1.37
				$message = Linker::formatComment( $comment->message->text() );
			}

			$editSummaries[] = [
				'humanTimestamp' => $this->context->getLanguage()
					->getHumanTimestamp( new MWTimestamp( $revision->getTimestamp() ) ),
				'timestamp' => $revision->getTimestamp(),
				'summary' => $message,
				'revisionId' => $revision->getId()
			];
		}

		return $editSummaries;
	}
}
