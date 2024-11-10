<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Page\PageIdentity;
use Wikimedia\Rdbms\IConnectionProvider;
use Wikimedia\Rdbms\SelectQueryBuilder;

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
	/** Stores the revision id of the corresponding source text. Used for showing diffs for outdated messages. */
	public const TRANSVER_PROP = 'tp:transver';
	/** Indicates a revision of a page that can be marked for translation. */
	public const TP_MARK_TAG = 'tp:mark';
	/** Indicates a revision of a translatable page that is marked for translation. */
	public const TP_READY_TAG = 'tp:tag';
	/** Indicates a revision of a page that is a valid message bundle. */
	public const MB_VALID_TAG = 'mb:valid';

	private IConnectionProvider $dbProvider;
	private array $tagCache = [];

	public function __construct( IConnectionProvider $dbProvider ) {
		$this->dbProvider = $dbProvider;
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

		$dbw = $this->dbProvider->getPrimaryDatabase();
		$conditions = [
			'rt_page' => $articleId,
			'rt_type' => $tag
		];
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'revtag' )
			->where( $conditions )
			->caller( __METHOD__ )
			->execute();

		if ( $value !== null ) {
			$conditions['rt_value'] = serialize( implode( '|', $value ) );
		}

		$conditions['rt_revision'] = $revisionId;
		$dbw->newInsertQueryBuilder()
			->insertInto( 'revtag' )
			->row( $conditions )
			->caller( __METHOD__ )
			->execute();

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
			->select( [ 'rt_revision' => 'MAX(rt_revision)', 'rt_type' ] )
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

		$dbw = $this->dbProvider->getPrimaryDatabase();
		$dbw->newDeleteQueryBuilder()
			->deleteFrom( 'revtag' )
			->where( [
				'rt_page' => $articleId,
				'rt_type' => $tag,
			] )
			->caller( __METHOD__ )
			->execute();

		unset( $this->tagCache[$articleId] );
	}

	public function isRevIdFuzzy( int $articleId, int $revisionId ): bool {
		$dbw = $this->dbProvider->getPrimaryDatabase();
		$res = $dbw->newSelectQueryBuilder()
			->select( 'rt_type' )
			->from( 'revtag' )
			->where( [
				'rt_page' => $articleId,
				'rt_type' => self::FUZZY_TAG,
				'rt_revision' => $revisionId
			] )
			->caller( __METHOD__ )
			->fetchField();

		return $res !== false;
	}

	/**
	 * Get the revision ID of the original message that was live the last
	 * time a non-fuzzy translation was saved (presumably the version whose
	 * translation the translation is). Used to determine whether the
	 * translation is outdated and to show a diff of the original message
	 * if it is.
	 *
	 * If a revision ID argument is specified, then ignore all versions after
	 * that revision ID in the above calculation.
	 *
	 * @return int|null The revision ID, or `null` if none is found
	 */
	public function getTransver( PageIdentity $identity, ?int $revid = null ): ?int {
		$db = Utilities::getSafeReadDB();
		$query = $db->newSelectQueryBuilder()
			->select( 'rt_value' )
			->from( 'revtag' )
			->where( [
				'rt_page' => $identity->getId(),
				'rt_type' => self::TRANSVER_PROP,
			] );
		if ( $revid !== null ) {
			$query->where( $db->expr( 'rt_revision', '<=', $revid ) );
		}
		$result = $query
			->orderBy( 'rt_revision', SelectQueryBuilder::SORT_DESC )
			->caller( __METHOD__ )
			->fetchField();
		if ( $result === false ) {
			return null;
		} else {
			// The revtag database is defined to store a string in rt_value,
			// but tp:transver is always an integer
			return (int)$result;
		}
	}

	/**
	 * Sets the tp:transver revtag of the given page/revision. This
	 * normally represents the last time a non-fuzzy translation was saved
	 * (presumably the version whose translation the translation is).
	 * and is used to determine whether the translation is outdated
	 * and to show a diff of the original message if it is.
	 * @return int|null The revision ID, or `null` if none is found
	 */
	public function setTransver( PageIdentity $identity, int $translationRevision, int $transver ) {
		$dbw = $this->dbProvider->getPrimaryDatabase();

		$conds = [
			'rt_page' => $identity->getId(),
			'rt_type' => self::TRANSVER_PROP,
			'rt_revision' => $translationRevision,
			'rt_value' => $transver,
		];
		$dbw->newReplaceQueryBuilder()
			->replaceInto( 'revtag' )
			->uniqueIndexFields( [ 'rt_type', 'rt_page', 'rt_revision' ] )
			->row( $conds )
			->caller( __METHOD__ )
			->execute();
	}

	/** Get a list of page ids where the latest revision is either tagged or marked */
	public static function getTranslatableBundleIds( string ...$revTags ): array {
		$dbr = Utilities::getSafeReadDB();
		$res = $dbr->newSelectQueryBuilder()
			->select( 'rt_page' )
			->from( 'revtag' )
			->join(
				'page',
				null,
				[ 'rt_page = page_id', 'rt_revision = page_latest', 'rt_type' => $revTags ]
			)
			->groupBy( 'rt_page' )
			->caller( __METHOD__ )
			->fetchResultSet();
		$results = [];
		foreach ( $res as $row ) {
			$results[] = (int)$row->rt_page;
		}

		return $results;
	}
}
