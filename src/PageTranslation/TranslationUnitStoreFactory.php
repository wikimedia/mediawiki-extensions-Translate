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
	private IConnectionProvider $connectionProvider;

	public function __construct( IConnectionProvider $connectionProvider ) {
		$this->connectionProvider = $connectionProvider;
	}

	public function getReader( PageIdentity $page ): TranslationUnitReader {
		$pageId = $page->getId();
		if ( $pageId === 0 ) {
			throw new LogicException( 'Page must exist' );
		}

		return new TranslationUnitStore( $this->connectionProvider->getReplicaDatabase(), $pageId );
	}
}
