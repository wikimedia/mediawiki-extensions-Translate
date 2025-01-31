<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Closure;
use DumpStringOutput;
use MediaWiki\Export\WikiExporterFactory;
use MediaWiki\Title\Title;
use WikiExporter;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * Service to export a translatable bundle into XML. Uses WikiExporter from MediaWiki core.
 * @since 2023.05
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class TranslatableBundleExporter {
	private WikiExporter $wikiExporter;
	private SubpageListBuilder $subpageListBuilder;
	private ?Closure $exportPageCallback;

	public function __construct(
		SubpageListBuilder $subpageListBuilder,
		WikiExporterFactory $wikiExporterFactory,
		IConnectionProvider $dbProvider
	) {
		$this->subpageListBuilder = $subpageListBuilder;
		$this->wikiExporter = $wikiExporterFactory->getWikiExporter(
			$dbProvider->getReplicaDatabase(),
			WikiExporter::FULL
		);
	}

	public function export( TranslatableBundle $bundle, bool $includeTalkPages, bool $includeSubPages ): string {
		$classifiedSubpages = $this->subpageListBuilder->getSubpagesPerType( $bundle, $includeTalkPages );

		$sink = new DumpStringOutput();
		$this->wikiExporter->setOutputSink( $sink );
		$this->wikiExporter->openStream();

		// Add all the pages to be exported
		$this->exportPages( [ $bundle->getTitle() ], 'translatable bundle' );
		$this->exportPages( $classifiedSubpages[ 'translationPages' ], 'translation' );
		$this->exportPages( $classifiedSubpages[ 'translationUnitPages' ], 'translation unit' );

		// Filter out null values. Null values mean that there is no corresponding talk page
		$talkPages = $includeTalkPages ? $classifiedSubpages[ 'talkPages' ] : [];
		$talkPages = array_filter( $talkPages, static function ( $val ) {
			return $val !== null;
		} );
		$this->exportPages( $talkPages, 'talk pages' );

		$this->exportPages(
			$includeTalkPages ? $classifiedSubpages[ 'translatableTalkPages' ] : [],
			'translatable talk',
		);
		$this->exportPages(
			$includeSubPages ? $classifiedSubpages[ 'normalSubpages' ] : [],
			'subpage'
		);

		$this->wikiExporter->closeStream();

		return (string)$sink;
	}

	public function setExportPageCallback( callable $callable ) {
		$this->exportPageCallback = Closure::fromCallable( $callable );
	}

	/**
	 * @param Title[] $pagesForExport
	 * @param string $pageType
	 */
	private function exportPages( array $pagesForExport, string $pageType ): void {
		if ( $this->exportPageCallback ) {
			( $this->exportPageCallback )( $pagesForExport, $pageType );
		}

		foreach ( $pagesForExport as $page ) {
			$this->wikiExporter->pageByTitle( $page );
		}
	}
}
