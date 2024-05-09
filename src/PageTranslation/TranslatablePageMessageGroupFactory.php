<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use CacheDependency;
use GlobalDependency;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\MessageGroupProcessing\CachedMessageGroupFactory;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Title\Title;
use MessageGroup;
use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\IReadableDatabase;
use WikiPageMessageGroup;

/**
 * Translatable page message group factories that uses caching.
 * @since 2024.05
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
final class TranslatablePageMessageGroupFactory implements CachedMessageGroupFactory {
	public const SERVICE_OPTIONS = [
		'EnablePageTranslation'
	];

	private ServiceOptions $options;
	private IReadableDatabase $db;

	public function __construct(
		ServiceOptions $options,
		IReadableDatabase $db
	) {
		$this->db = $db;
		$options->assertRequiredOptions( self::SERVICE_OPTIONS );
		$this->options = $options;
	}

	public function getCacheKey(): string {
		return 'translatable-pages';
	}

	public function getCacheVersion(): int {
		return 1;
	}

	/** @return CacheDependency[] */
	public function getDependencies(): array {
		return [ new GlobalDependency( 'wgEnablePageTranslation' ) ];
	}

	/** @return string[] */
	public function getData( bool $recache, array &$setOpts ): array {
		if ( !$this->options->get( 'EnablePageTranslation' ) ) {
			return [];
		}

		if ( !$recache ) {
			$setOpts += Database::getCacheSetOptions( $this->db );
		}

		$groupTitles = [];
		$res = $this->db->newSelectQueryBuilder()
			->select( [ 'page_id', 'page_namespace', 'page_title' ] )
			->from( 'page' )
			->join( 'revtag', null, 'page_id=rt_page' )
			->where( [ 'rt_type' => RevTagStore::TP_MARK_TAG ] )
			->caller( __METHOD__ )
			->groupBy( [ 'rt_page', 'page_id', 'page_namespace', 'page_title' ] )
			->fetchResultSet();

		foreach ( $res as $r ) {
			$title = Title::newFromRow( $r );
			$groupTitles[] = $title->getPrefixedText();
		}

		return $groupTitles;
	}

	/**
	 * @param string[] $data
	 * @return MessageGroup[]
	 */
	public function createGroups( $data ): array {
		$groups = [];
		foreach ( $data as $title ) {
			$title = Title::newFromText( $title );
			$id = TranslatablePage::getMessageGroupIdFromTitle( $title );
			$groups[$id] = new WikiPageMessageGroup( $id, $title );
		}

		return $groups;
	}
}
