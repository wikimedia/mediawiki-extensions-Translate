<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Page\PageIdentity;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * Class to manage revision tags for translatable bundles.
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.04
 * @license GPL-2.0-or-later
 */
class RevTagStore {
	/** Indicates that a translation is fuzzy (outdated or not passing validation). */
	public const FUZZY_TAG = 'fuzzy';
	/** Stores the revision id of the source text which was translated. Used for showing
	 * diffs for outdated messages.
	 */
	public const TRANSVER_PROP = 'tp:transver';
	/** Indicates a revision of a page that can be marked for translation. */
	public const TP_MARK_TAG = 'tp:mark';
	/** Indicates a revision of a translatable page that is marked for translation. */
	public const TP_READY_TAG = 'tp:tag';
	/** Indicates a revision of a page that is a valid message bundle. */
	public const MB_VALID_TAG = 'mb:valid';

	private IConnectionProvider $connectionProvider;
	/** @var array */
	private $tagCache = [];

	public function __construct( IConnectionProvider $connectionProvider ) {
		$this->connectionProvider = $connectionProvider;
	}

	/** Add tag for the given revisionId, while deleting it from others */
	public function replaceTag(
		PageIdentity $identity,
		string $tag,
		int $revisionId,
		?array $value = null
	): void {
		if ( !$identity->exists() ) {
			return;
		}

		$articleId = $identity->getId();

		$dbw = $this->connectionProvider->getPrimaryDatabase();
		$conds = [
			'rt_page' => $articleId,
			'rt_type' => $tag
		];
		$dbw->delete( 'revtag', $conds, __METHOD__ );

		if ( $value !== null ) {
			$conds['rt_value'] = serialize( implode( '|', $value ) );
		}

		$conds['rt_revision'] = $revisionId;
		$dbw->insert( 'revtag', $conds, __METHOD__ );

		$this->tagCache[$articleId][$tag] = $revisionId;
	}

	public function getLatestRevisionWithTag( PageIdentity $identity, string $tag ): ?int {
		$response = $this->getLatestRevisionsForTags( $identity, $tag );
		return $response[$tag] ?? null;
	}

	/** @return null|int[] */
	public function getLatestRevisionsForTags( PageIdentity $identity, string ...$tags ): ?array {
		if ( !$identity->exists() ) {
			return null;
		}

		$articleId = $identity->getId();

		$response = [];
		$remainingTags = [];

		// ATTENTION: Cache should only be updated on POST requests.
		foreach ( $tags as $tag ) {
			if ( isset( $this->tagCache[$articleId][$tag] ) ) {
				$response[$tag] = $this->tagCache[$articleId][$tag];
			} else {
				$remainingTags[] = $tag;
			}
		}

		if ( !$remainingTags ) {
			// All tags were available in the cache, no need to run any queries.
			return $response;
		}

		$dbr = Utilities::getSafeReadDB();
		$results = $dbr->newSelectQueryBuilder()
			->select( [ 'MAX(rt_revision) AS rt_revision', 'rt_type' ] )
			->from( 'revtag' )
			->where( [
				'rt_page' => $articleId,
				'rt_type' => $remainingTags
			] )
			->groupBy( 'rt_type' )
			->caller( __METHOD__ )
			->fetchResultSet();

		foreach ( $results as $row ) {
			$response[$row->rt_type] = (int)$row->rt_revision;
		}

		return $response;
	}

	public function removeTags( PageIdentity $identity, string ...$tag ): void {
		if ( !$identity->exists() ) {
			return;
		}

		$articleId = $identity->getId();

		$dbw = $this->connectionProvider->getPrimaryDatabase();
		$conds = [
			'rt_page' => $articleId,
			'rt_type' => $tag,
		];
		$dbw->delete( 'revtag', $conds, __METHOD__ );

		unset( $this->tagCache[$articleId] );
	}

	/** Get a list of page ids where the latest revision is either tagged or marked */
	public static function getTranslatableBundleIds( string ...$revTags ): array {
		$dbr = Utilities::getSafeReadDB();
		$res = $dbr->newSelectQueryBuilder()
			->select( 'rt_page' )
			->tables( [ 'revtag', 'page' ] )
			->where( [
				'rt_page = page_id',
				'rt_revision = page_latest',
				'rt_type' => $revTags,
			] )
			->groupBy( 'rt_page' )
			->caller( __METHOD__ )
			->fetchResultSet();
		$results = [];
		foreach ( $res as $row ) {
			$results[$row->rt_page] = true;
		}

		return $results;
	}
}
