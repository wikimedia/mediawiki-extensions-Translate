<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use LogicException;
use Title;
use Wikimedia\Rdbms\ILoadBalancer;
use const DB_PRIMARY;
use const DB_REPLICA;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class TranslationUnitStoreFactory {
	/** @var ILoadBalancer */
	private $lb;

	public function __construct( ILoadBalancer $lb ) {
		$this->lb = $lb;
	}

	public function getReader( Title $page ): TranslationUnitReader {
		$pageId = $page->getArticleID();
		if ( $pageId === 0 ) {
			throw new LogicException( 'Page must exist' );
		}

		return new TranslationUnitStore( $this->lb->getConnectionRef( DB_REPLICA ), $pageId );
	}

	public function getWriter( Title $page ): TranslationUnitStore {
		$pageId = $page->getArticleID();
		if ( $pageId === 0 ) {
			throw new LogicException( 'Page must exist' );
		}

		return new TranslationUnitStore( $this->lb->getConnectionRef( DB_PRIMARY ), $pageId );
	}
}
