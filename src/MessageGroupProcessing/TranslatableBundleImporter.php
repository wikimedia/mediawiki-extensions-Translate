<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Closure;
use Exception;
use ImportStreamSource;
use ManualLogEntry;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageParser;
use MediaWiki\Page\PageIdentity;
use MediaWiki\Permissions\UltimateAuthority;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use TextContent;
use WikiImporterFactory;

/**
 * Service to import a translatable bundle from a file. Uses WikiImporter from MediaWiki core.
 * @since 2023.05
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class TranslatableBundleImporter {
	private WikiImporterFactory $wikiImporterFactory;
	private TranslatablePageParser $translatablePageParser;
	private RevisionLookup $revisionLookup;
	private ?Title $bundleTitle;
	private ?Closure $importCompleteCallback;

	public function __construct(
		WikiImporterFactory $wikiImporterFactory,
		TranslatablePageParser $translatablePageParser,
		RevisionLookup $revisionLookup
	) {
		$this->wikiImporterFactory = $wikiImporterFactory;
		$this->translatablePageParser = $translatablePageParser;
		$this->revisionLookup = $revisionLookup;
	}

	public function import(
		string $importFilePath,
		string $interwikiPrefix,
		bool $assignKnownUsers,
		UserIdentity $user,
		?string $comment
	): Title {
		$importSource = ImportStreamSource::newFromFile( $importFilePath );
		if ( !$importSource->isOK() ) {
			throw new TranslatableBundleImportException(
				"Error while reading import file '$importFilePath': " . $importSource->getMessage()->text()
			);
		}

		$wikiImporter = $this->wikiImporterFactory
			// This is used only in a maintenance script (importTranslatableBundle.php),
			// so use UltimateAuthority to skip permission checks
			->getWikiImporter( $importSource->value, new UltimateAuthority( $user ) );
		$wikiImporter->setUsernamePrefix( $interwikiPrefix, $assignKnownUsers );
		$wikiImporter->setPageOutCallback( [ $this, 'pageCallback' ] );

		try {
			// Reset the currently set title which might have been set during the previous import process
			$this->bundleTitle = null;
			$importResult = $wikiImporter->doImport();
		} catch ( Exception $e ) {
			throw new TranslatableBundleImportException(
				$e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		if ( $importResult === false ) {
			throw new TranslatableBundleImportException( 'Unknown error while importing translatable bundle.' );
		}

		if ( !$this->bundleTitle ) {
			throw new TranslatableBundleImportException( 'Import done, but could not identify imported page.' );
		}

		if ( $this->importCompleteCallback ) {
			// Import is complete
			call_user_func( $this->importCompleteCallback, $this->bundleTitle );
		}

		// WikiImporter does not trigger hooks that run after a page is edited. Hence, manually add the ready
		// tag to the imported page if it contains the markup
		$this->addReadyTagForTranslatablePage( $this->bundleTitle );
		$this->logImport( $user, $this->bundleTitle, $comment );

		return $this->bundleTitle;
	}

	public function pageCallback( PageIdentity $pageIdentity ): void {
		// We assume that the first PageIdentity that we receive is the translatable bundle being imported.
		$this->bundleTitle ??= Title::newFromPageIdentity( $pageIdentity );
	}

	public function setImportCompleteCallback( callable $callable ): void {
		$this->importCompleteCallback = Closure::fromCallable( $callable );
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
}
