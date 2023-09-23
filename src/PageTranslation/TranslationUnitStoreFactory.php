<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use LogicException;
use MediaWiki\Page\PageIdentity;
use Wikimedia\Rdbms\ILoadBalancer;
use const DB_PRIMARY;
use const DB_REPLICA;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class TranslationUnitStoreFactory {
	private ILoadBalancer $lb;

	public function __construct( ILoadBalancer $lb ) {
		$this->lb = $lb;
	}

	public function getReader( PageIdentity $page ): TranslationUnitReader {
		$pageId = $page->getId();
		if ( $pageId === 0 ) {
			throw new LogicException( 'Page must exist' );
		}

		return new TranslationUnitStore( $this->lb->getConnection( DB_REPLICA ), $pageId );
	}

	public function getWriter( PageIdentity $page ): TranslationUnitStore {
		$pageId = $page->getId();
		if ( $pageId === 0 ) {
			throw new LogicException( 'Page must exist' );
		}

		return new TranslationUnitStore( $this->lb->getConnection( DB_PRIMARY ), $pageId );
	}
}
