<?php
/**
 * Translation aid code.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use Wikimedia\Rdbms\IDatabase;

/** @since 2018.01 */
class TranslationAidDataProvider {
	private $handle;
	private $group;
	private $definition;
	private $translations;

	public function __construct( MessageHandle $handle ) {
		$this->handle = $handle;
		$this->group = $handle->getGroup();
	}

	/**
	 * Get the message definition. Cached for performance.
	 *
	 * @return string
	 */
	public function getDefinition() {
		if ( $this->definition !== null ) {
			return $this->definition;
		}

		// Optional performance optimization
		if ( is_callable( [ $this->group, 'getMessageContent' ] ) ) {
			// @phan-suppress-next-line PhanUndeclaredMethod
			$this->definition = $this->group->getMessageContent( $this->handle );
		} else {
			$this->definition = $this->group->getMessage(
				$this->handle->getKey(),
				$this->group->getSourceLanguage()
			);
		}

		return $this->definition;
	}

	/** @return Content */
	public function getDefinitionContent() {
		return ContentHandler::makeContent( $this->getDefinition(), $this->handle->getTitle() );
	}

	/**
	 * Get the translations in all languages. Cached for performance.
	 * Fuzzy translation are not included.
	 *
	 * @return array Language code => Translation
	 */
	public function getGoodTranslations() {
		if ( $this->translations !== null ) {
			return $this->translations;
		}

		$data = self::loadTranslationData( wfGetDB( DB_REPLICA ), $this->handle );
		$translations = [];
		$prefixLength = strlen( $this->handle->getTitleForBase()->getDBKey() . '/' );

		foreach ( $data as $page => $translation ) {
			// Could use MessageHandle here, but that queries the message index.
			// Instead we can get away with simple string manipulation.
			$code = substr( $page, $prefixLength );
			if ( !Language::isKnownLanguageTag( $code ) ) {
				continue;
			}

			$translations[ $code ] = $translation;
		}

		$this->translations = $translations;

		return $translations;
	}

	private static function loadTranslationData( IDatabase $db, MessageHandle $handle ) {
		$revisionStore = MediaWikiServices::getInstance()->getRevisionStore();
		if ( is_callable( [ $revisionStore, 'newRevisionsFromBatch' ] ) ) {
			$queryInfo = $revisionStore->getQueryInfo( [ 'page' ] );
			$tables = $queryInfo[ 'tables' ];
			$fields = $queryInfo[ 'fields' ];
			$conds = [];
			$options = [];
			$joins = $queryInfo[ 'joins' ];
		} else {
			$queryInfo = Revision::getQueryInfo( [ 'page', 'text' ] );
			$tables = $queryInfo[ 'tables' ];
			$fields = $queryInfo[ 'fields' ];
			$conds = [];
			$options = [];
			$joins = $queryInfo[ 'joins' ];
		}

		// The list of pages we want to select, and their latest versions
		$conds['page_namespace'] = $handle->getTitle()->getNamespace();
		$base = $handle->getKey();
		$conds[] = 'page_title ' . $db->buildLike( "$base/", $db->anyString() );
		$conds[] = 'rev_id=page_latest';

		// For fuzzy tags we also need:
		$tables[] = 'revtag';
		$conds[ 'rt_type' ] = null;
		$joins[ 'revtag' ] = [
			'LEFT JOIN',
			[ 'page_id=rt_page', 'page_latest=rt_revision', 'rt_type' => 'fuzzy' ]
		];

		$rows = $db->select( $tables, $fields, $conds, __METHOD__, $options, $joins );

		$pages = [];
		if ( is_callable( [ $revisionStore, 'newRevisionsFromBatch' ] ) ) {
			$revisions = $revisionStore->newRevisionsFromBatch( $rows, [
				'slots' => [ SlotRecord::MAIN ]
			] )->getValue();
			foreach ( $rows as $row ) {
				/** @var RevisionRecord|null $rev */
				$rev = $revisions[$row->rev_id];
				if ( $rev && $rev->getContent( SlotRecord::MAIN ) instanceof TextContent ) {
					$pages[$row->page_title] = $rev->getContent( SlotRecord::MAIN )->getText();
				}
			}
		} else {
			foreach ( $rows as $row ) {
				$pages[$row->page_title] = Revision::getRevisionText( $row );
			}
		}
		return $pages;
	}
}
