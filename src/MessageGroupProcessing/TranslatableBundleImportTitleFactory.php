<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use InvalidArgumentException;
use MediaWiki\Extension\Translate\PageTranslation\PageTitleRenamer;
use MediaWiki\Title\ForeignTitle;
use MediaWiki\Title\ImportTitleFactory;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;

/**
 * A parser that translates page titles from a foreign wiki into titles on the local wiki,
 * keeping a specified target page in mind
 * @since 2024.02
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class TranslatableBundleImportTitleFactory implements ImportTitleFactory {
	private NamespaceInfo $namespaceInfo;
	private PageTitleRenamer $pageTitleRenamer;
	private Title $targetPage;
	private TitleFactory $titleFactory;
	private ForeignTitle $sourcePage;

	public function __construct( NamespaceInfo $namespaceInfo, TitleFactory $titleFactory, Title $targetPage ) {
		$this->namespaceInfo = $namespaceInfo;
		$this->targetPage = $targetPage;
		$this->titleFactory = $titleFactory;
	}

	public function createTitleFromForeignTitle( ForeignTitle $foreignTitle ): Title {
		if ( !$foreignTitle->isNamespaceIdKnown() ) {
			throw new InvalidArgumentException(
				"Unable to determine namespace for foreign title {$foreignTitle}"
			);
		}

		$foreignTitleNamespaceId = $foreignTitle->getNamespaceId();
		$foreignTitleText = $foreignTitle->getText();

		if ( !$this->namespaceInfo->exists( $foreignTitleNamespaceId ) ) {
			throw new InvalidArgumentException(
				"The foreign title $foreignTitle has a namespace that does not exist in the current wiki.\n" .
				__CLASS__ . " does not support this functionality yet."
			);
		}

		$titleFromForeignTitle = $this->titleFactory->makeTitle( $foreignTitleNamespaceId, $foreignTitleText );
		// Assume that the first title is the source title
		$this->sourcePage ??= $foreignTitle;
		$this->pageTitleRenamer ??= new PageTitleRenamer( $titleFromForeignTitle, $this->targetPage );

		return $this->pageTitleRenamer->getNewTitle( $titleFromForeignTitle );
	}
}
