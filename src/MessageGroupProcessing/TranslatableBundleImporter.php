<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Closure;
use Exception;
use ImportStreamSource;
use ManualLogEntry;
use MediaWiki\Content\TextContent;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageParser;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Hook\AfterImportPageHook;
use MediaWiki\Language\FormatterFactory;
use MediaWiki\Permissions\UltimateAuthority;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\NamespaceInfo;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFactory;
use MediaWiki\User\UserIdentity;
use MessageLocalizer;
use WikiImporterFactory;

/**
 * Service to import a translatable bundle from a file. Uses WikiImporter from MediaWiki core.
 * @since 2023.05
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class TranslatableBundleImporter implements AfterImportPageHook {
	private WikiImporterFactory $wikiImporterFactory;
	private TranslatablePageParser $translatablePageParser;
	private RevisionLookup $revisionLookup;
	private ?Title $bundleTitle;
	private ?Closure $pageImportCompleteCallback = null;
	private NamespaceInfo $namespaceInfo;
	private TitleFactory $titleFactory;
	private FormatterFactory $formatterFactory;
	private bool $importInProgress = false;

	public function __construct(
		WikiImporterFactory $wikiImporterFactory,
		TranslatablePageParser $translatablePageParser,
		RevisionLookup $revisionLookup,
		NamespaceInfo $namespaceInfo,
		TitleFactory $titleFactory,
		FormatterFactory $formatterFactory
	) {
		$this->wikiImporterFactory = $wikiImporterFactory;
		$this->translatablePageParser = $translatablePageParser;
		$this->revisionLookup = $revisionLookup;
		$this->namespaceInfo = $namespaceInfo;
		$this->titleFactory = $titleFactory;
		$this->formatterFactory = $formatterFactory;
	}

	/** Factory method used to initialize this HookHandler */
	public static function getInstance(): self {
		return Services::getInstance()->getTranslatableBundleImporter();
	}

	public function import(
		string $importFilePath,
		string $interwikiPrefix,
		bool $assignKnownUsers,
		UserIdentity $user,
		MessageLocalizer $localizer,
		?Title $targetPage,
		?string $comment
	): Title {
		$importSource = ImportStreamSource::newFromFile( $importFilePath );
		if ( !$importSource->isOK() ) {
			throw new TranslatableBundleImportException(
				"Error while reading import file '$importFilePath': " .
				$this->formatterFactory->getStatusFormatter( $localizer )->getMessage( $importSource )->text()
			);
		}

		$wikiImporter = $this->wikiImporterFactory
			// This is used only in a maintenance script (importTranslatableBundle.php),
			// so use UltimateAuthority to skip permission checks
			->getWikiImporter( $importSource->value, new UltimateAuthority( $user ) );
		$wikiImporter->setUsernamePrefix( $interwikiPrefix, $assignKnownUsers );

		if ( $targetPage !== null ) {
			$wikiImporter->setImportTitleFactory(
				new TranslatableBundleImportTitleFactory( $this->namespaceInfo, $this->titleFactory, $targetPage )
			);
		}

		try {
			$this->importInProgress = true;
			// Reset the currently set title which might have been set during the previous import process
			$this->bundleTitle = null;
			$importResult = $wikiImporter->doImport();
		} catch ( Exception $e ) {
			throw new TranslatableBundleImportException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		} finally {
			$this->importInProgress = false;
		}

		if ( $importResult === false ) {
			throw new TranslatableBundleImportException( 'Unknown error while importing translatable bundle.' );
		}

		if ( !$this->bundleTitle ) {
			throw new TranslatableBundleImportException( 'Import done, but could not identify imported page.' );
		}

		// WikiImporter does not trigger hooks that run after a page is edited. Hence, manually add the ready
		// tag to the imported page if it contains the markup
		$this->addReadyTagForTranslatablePage( $this->bundleTitle );
		$this->logImport( $user, $this->bundleTitle, $comment );

		return $this->bundleTitle;
	}

	public function setPageImportCompleteCallback( callable $callable ): void {
		$this->pageImportCompleteCallback = Closure::fromCallable( $callable );
	}

	private function logImport( UserIdentity $user, Title $bundle, ?string $comment ): void {
		$entry = new ManualLogEntry( 'import', 'translatable-bundle' );
		$entry->setPerformer( $user );
		$entry->setTarget( $bundle );
		$logId = $entry->insert();
		if ( $comment ) {
			$entry->setComment( $comment );
		}
		$entry->publish( $logId );
	}

	/** Add ready tag in case the page imported has <translate> markup */
	private function addReadyTagForTranslatablePage( Title $translatablePageTitle ) {
		$revisionRecord = $this->revisionLookup->getRevisionByTitle( $translatablePageTitle );
		if ( !$revisionRecord ) {
			throw new TranslatableBundleImportException(
				"Revision record could not be found for imported page: $translatablePageTitle"
			);
		}

		$content = $revisionRecord->getContent( SlotRecord::MAIN );
		if ( !$content instanceof TextContent ) {
			throw new TranslatableBundleImportException(
				"Content in revision record for $translatablePageTitle is not of type TextContent"
			);
		}

		if ( $this->translatablePageParser->containsMarkup( $content->getText() ) ) {
			// Add the ready tag
			$page = TranslatablePage::newFromTitle( Title::newFromLinkTarget( $translatablePageTitle ) );
			$page->addReadyTag( $revisionRecord->getId() );
		}
	}

	/** @inheritDoc */
	public function onAfterImportPage( $title, $foreignTitle, $revCount, $sRevCount, $pageInfo ) {
		if ( $this->importInProgress ) {
			$this->bundleTitle ??= $title;
			if ( $this->pageImportCompleteCallback ) {
				( $this->pageImportCompleteCallback )( $title, $foreignTitle );
			}
		}
	}
}
