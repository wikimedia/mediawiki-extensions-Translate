<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageProcessing;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleArrayFromResult;

/**
 * Helper class that contains utility methods to help with identifying and replace titles.
 * @author Abijeet Patro
 * @since 2019.10
 * @license GPL-2.0-or-later
 */
class TranslateReplaceTitle {
	/**
	 * Returns two lists: a set of message handles that would be moved/renamed by
	 * the current text replacement, and the set of message handles that would ordinarily
	 * be moved but are not movable, due to permissions or any other reason.
	 * @return Title[][]
	 */
	public static function getTitlesForMove(
		MessageHandle $sourceMessageHandle, string $replacement
	): array {
		$titlesForMove = [];
		$namespace = $sourceMessageHandle->getTitle()->getNamespace();

		$titles = self::getMatchingTitles( $sourceMessageHandle );

		foreach ( $titles as $title ) {
			$handle = new MessageHandle( $title );
			// This takes care of situations where we have two different titles
			// foo and foo/bar, both will be matched and fetched but the slash
			// does not represent a language separator
			if ( $handle->getKey() !== $sourceMessageHandle->getKey() ) {
				continue;
			}
			$targetTitle = Title::makeTitle(
				$namespace,
				Utilities::title( $replacement, $handle->getCode(), $namespace )
			);
			$titlesForMove[] = [ $title, $targetTitle ];
		}

		return $titlesForMove;
	}

	private static function getMatchingTitles( MessageHandle $handle ): TitleArrayFromResult {
		$dbr = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_PRIMARY );
		$result = $dbr->newSelectQueryBuilder()
			->select( [ 'page_title', 'page_namespace', 'page_id' ] )
			->from( 'page' )
			->where( [
				'page_namespace' => $handle->getTitle()->getNamespace(),
				'page_title ' . $dbr->buildLike(
					$handle->getTitleForBase()->getDBkey(), '/', $dbr->anyString()
				),
			] )
			->caller( __METHOD__ )
			->fetchResultSet();

		return new TitleArrayFromResult( $result );
	}
}
