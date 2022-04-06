<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Page\PageIdentity;

/**
 * Class to manage revision tags for translatable bundles.
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.04
 * @license GPL-2.0-or-later
 */
class RevTagStore {
	// TODO: Convert to a normal member variable once RevTagStore is a service.
	/** @var array */
	private static $tagCache = [];

	public function addTag( PageIdentity $identity, string $tag, int $revisionId, ?array $value = null ): void {
		if ( !$identity->exists() ) {
			return;
		}

		$articleId = $identity->getId();

		$dbw = wfGetDB( DB_PRIMARY );
		$conds = [
			'rt_page' => $articleId,
			'rt_type' => $tag,
			'rt_revision' => $revisionId
		];
		$dbw->delete( 'revtag', $conds, __METHOD__ );

		if ( $value !== null ) {
			$conds['rt_value'] = serialize( implode( '|', $value ) );
		}

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
}
