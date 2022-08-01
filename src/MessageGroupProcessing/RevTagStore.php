<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Page\PageIdentity;
use TranslateUtils;

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

	// TODO: Convert to a normal member variable once RevTagStore is a service.
	/** @var array */
	private static $tagCache = [];

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

		$dbw = wfGetDB( DB_PRIMARY );
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

		self::$tagCache[$articleId][$tag] = $revisionId;
	}

	public function getLatestRevisionWithTag( PageIdentity $identity, string $tag ): ?int {
		if ( !$identity->exists() ) {
			return null;
		}

		$articleId = $identity->getId();

		// ATTENTION: Cache should only be updated on POST requests.
		if ( isset( self::$tagCache[$articleId][$tag] ) ) {
			return self::$tagCache[$articleId][$tag];
		}

		$db = wfGetDB( DB_REPLICA );
		$conds = [
			'rt_page' => $articleId,
			'rt_type' => $tag
		];

		$options = [ 'ORDER BY' => 'rt_revision DESC' ];
		$value = $db->selectField( 'revtag', 'rt_revision', $conds, __METHOD__, $options );

		return $value === false ? null : (int)$value;
	}

	public function removeTags( PageIdentity $identity, string ...$tag ): void {
		if ( !$identity->exists() ) {
			return;
		}

		$articleId = $identity->getId();

		$dbw = wfGetDB( DB_PRIMARY );
		$conds = [
			'rt_page' => $articleId,
			'rt_type' => $tag,
		];
		$dbw->delete( 'revtag', $conds, __METHOD__ );

		unset( self::$tagCache[$articleId] );
	}

	/** Get a list of page ids where the latest revision is either tagged or marked */
	public static function getTranslatableBundleIds( string ...$revTags ): array {
		$dbr = TranslateUtils::getSafeReadDB();

		$tables = [ 'revtag', 'page' ];
		$fields = 'rt_page';
		$conds = [
			'rt_page = page_id',
			'rt_revision = page_latest',
			'rt_type' => $revTags,
		];
		$options = [ 'GROUP BY' => 'rt_page' ];

		$res = $dbr->select( $tables, $fields, $conds, __METHOD__, $options );
		$results = [];
		foreach ( $res as $row ) {
			$results[] = (int)$row->rt_page;
		}

		return $results;
	}
}
