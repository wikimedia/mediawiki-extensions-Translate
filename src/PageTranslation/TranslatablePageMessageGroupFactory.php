<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use CacheDependency;
use MainConfigDependency;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\Extension\Translate\MessageGroupProcessing\CachedMessageGroupFactory;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Page\PageReferenceValue;
use MediaWiki\Title\Title;
use MessageGroup;
use Wikimedia\Rdbms\IReadableDatabase;
use WikiPageMessageGroup;

/**
 * @since 2024.05
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
final class TranslatablePageMessageGroupFactory implements CachedMessageGroupFactory {
	public const SERVICE_OPTIONS = [
		'EnablePageTranslation',
		'LanguageCode',
	];

	private ServiceOptions $options;

	public function __construct( ServiceOptions $options ) {
		$options->assertRequiredOptions( self::SERVICE_OPTIONS );
		$this->options = $options;
	}

	public function getCacheKey(): string {
		return 'translatable-pages';
	}

	public function getCacheVersion(): int {
		return 3;
	}

	/** @return CacheDependency[] */
	public function getDependencies(): array {
		return [ new MainConfigDependency( 'EnablePageTranslation' ) ];
	}

	/** @return array[] */
	public function getData( IReadableDatabase $db ): array {
		if ( !$this->options->get( 'EnablePageTranslation' ) ) {
			return [];
		}

		$data = [];
		$res = $db->newSelectQueryBuilder()
			->select( [ 'page_namespace', 'page_title', 'page_lang' ] )
			->from( 'page' )
			->join( 'revtag', null, 'page_id=rt_page' )
			->where( [ 'rt_type' => RevTagStore::TP_MARK_TAG ] )
			->caller( __METHOD__ )
			->groupBy( [ 'rt_page', 'page_namespace', 'page_title', 'page_lang' ] )
			->fetchResultSet();

		foreach ( $res as $r ) {
			$data[] = [ (int)$r->page_namespace, $r->page_title, $r->page_lang ];
		}

		return $data;
	}

	/**
	 * @param array[] $data
	 * @return MessageGroup[]
	 */
	public function createGroups( $data ): array {
		$defaultLanguage = $this->options->get( 'LanguageCode' );

		$groups = [];
		foreach ( $data as [ $namespace, $text, $language ] ) {
			$pageReference = PageReferenceValue::localReference( $namespace, $text );
			$title = Title::newFromPageReference( $pageReference );
			$id = TranslatablePage::getMessageGroupIdFromTitle( $title );
			$groups[$id] = new WikiPageMessageGroup( $id, $title, $language ?? $defaultLanguage );
		}

		return $groups;
	}
}
