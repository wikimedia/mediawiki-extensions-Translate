<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use MediaWiki\MediaWikiServices;
use MediaWiki\Utils\MWTimestamp;
use Wikimedia\Rdbms\SelectQueryBuilder;

/**
 * Translation aid that provides last X edit summaries for a translation
 *
 * @author Eugene Wang'ombe
 * @license GPL-2.0-or-later
 * @since 2022.04
 */
class EditSummariesAid extends TranslationAid {
	private const COMMENT_COUNT = 3;

	public function getData(): array {
		$pageTitle = $this->handle->getTitle();
		if ( !$pageTitle->exists() ) {
			return [];
		}

		$mwService = MediaWikiServices::getInstance();
		$revisionFactory = $mwService->getRevisionFactory();

		// Build the query to fetch the last x revisions
		$dbr = $mwService->getDBLoadBalancer()->getConnection( DB_REPLICA );
		$aid = $pageTitle->getArticleID();
		$result = $revisionFactory
			->newSelectQueryBuilder( $dbr )
			->joinComment()
			->where( [ 'rev_page' => $aid ] )
			->orderBy( [ 'rev_timestamp', 'rev_id' ], SelectQueryBuilder::SORT_DESC )
			->limit( self::COMMENT_COUNT )
			->caller( __METHOD__ )
			->fetchResultSet();

		$editSummaries = [];
		$commentFormatter = $mwService->getCommentFormatter();
		foreach ( $result as $row ) {
			$revision = $revisionFactory->newRevisionFromRow( $row );
			$comment = $revision->getComment();

			// The result of getComment() may return null. In that case
			// skip processing of the summary.
			if ( !$comment ) {
				continue;
			}

			$message = $commentFormatter->format( $comment->message->text() );

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
