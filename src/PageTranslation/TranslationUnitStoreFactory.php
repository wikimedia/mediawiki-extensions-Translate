<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use LogicException;
use MediaWiki\Page\PageIdentity;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class TranslationUnitStoreFactory {

	public function __construct(
		private readonly IConnectionProvider $connectionProvider,
	) {
	}

	/** Get a read-only store backed by a replica database connection. */
	public function getReader( PageIdentity $page ): TranslationUnitStore {
		$pageId = $page->getId();
		if ( $pageId === 0 ) {
			throw new LogicException( 'Page must exist' );
		}

		return new TranslationUnitStore( $this->connectionProvider->getReplicaDatabase(), $pageId );
	}

	/** Get a read-write store backed by a primary database connection. */
	public function getWriter( PageIdentity $page ): WritableTranslationUnitStore {
		$pageId = $page->getId();
		if ( $pageId === 0 ) {
			throw new LogicException( 'Page must exist' );
		}

		return new WritableTranslationUnitStore( $this->connectionProvider->getPrimaryDatabase(), $pageId );
	}
}
