<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageMarkException;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageSettings;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\ForeignTitle;
use MediaWiki\Title\MalformedTitleException;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use Wikimedia\Rdbms\IDBAccessObject;

/**
 * Script to import a translatable bundle from a script exported via WikiExporter.
 * @since 2023.05
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class ImportTranslatableBundleMaintenanceScript extends BaseMaintenanceScript {
	private int $pageImportCount = 0;
	private int $totalPagesBeingImported = 0;

	public function __construct() {
		parent::__construct();
		$this->addArg(
			'xml-path',
			'Path to the XML file to be imported',
			self::REQUIRED
		);
		$this->addOption(
			'user',
			'Name of the user performing the import',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'interwiki-prefix',
			'Prefix to apply to unknown (and possibly also known) usernames',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'comment',
			'Comment added to the log for the import',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'assign-known-users',
			'Whether to apply the prefix to usernames that exist locally',
			self::OPTIONAL
		);
		$this->addOption(
			'target-name',
			'Target page name to import the page to',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'override',
			'Override existing target page if it exists',
			self::OPTIONAL
		);

		// Options related to marking a page for translation
		$this->addOption(
			'skip-translating-title',
			'Should translation of title be skipped',
			self::OPTIONAL
		);
		$this->addOption(
			'priority-languages',
			'Comma separated list of priority language codes',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'priority-languages-reason',
			'Reason for setting the priority languages',
			self::OPTIONAL,
			self::HAS_ARG
		);
		$this->addOption(
			'force-priority-languages',
			'Only allow translations to the priority languages',
			self::OPTIONAL
		);
		$this->addOption(
			'disallow-transclusion',
			'Disable translation aware transclusion for this page',
			self::OPTIONAL
		);
		$this->addOption(
			'use-old-syntax-version',
			'Use the old syntax version for translatable pages',
			self::OPTIONAL
		);

		$this->requireExtension( 'Translate' );
	}

	/** @inheritDoc */
	public function execute() {
		$this->pageImportCount = 0;
		$importFilePath = $this->getPathOfFileToImport();
		$importUser = $this->getImportUser();
		$comment = $this->getOption( 'comment' );
		$interwikiPrefix = $this->getInterwikiPrefix();
		$assignKnownUsers = $this->hasOption( 'assign-known-users' );
		$targetPage = $this->getTargetPageName();
		$translatablePageSettings = $this->getTranslatablePageSettings();

		// First import the page
		try {
			$this->totalPagesBeingImported = substr_count( file_get_contents( $importFilePath ), '</page>' );
			$importer = Services::getInstance()->getTranslatableBundleImporter();
			$this->output( "Starting import for file '$importFilePath'...\n" );
			$importer->setPageImportCompleteCallback( [ $this, 'logPageImportComplete' ] );
			$bundleTitle = $importer->import(
				$importFilePath,
				$interwikiPrefix,
				$assignKnownUsers,
				$importUser,
				RequestContext::getMain(),
				$targetPage,
				$comment
			);
		} catch ( TranslatableBundleImportException $e ) {
			$this->error( "An error occurred during import: {$e->getMessage()}\n" );
			$this->error( "Stacktrace: {$e->getTraceAsString()} .\n" );
			$this->fatalError( 'Stopping import.' );
		}

		$this->output(
			"Translatable bundle {$bundleTitle->getPrefixedText()} was imported. " .
			"Total {$this->pageImportCount} page(s) were created\n"
		);

		$this->output( "Now marking {$bundleTitle->getPrefixedText()} for translation...\n" );
		// Try to mark the page for translation
		$this->markPageForTranslation( $bundleTitle, $translatablePageSettings, $importUser );
	}

	public function logPageImportComplete( Title $title, ForeignTitle $foreignTitle ): void {
		++$this->pageImportCount;
		$currentProgress = str_pad(
			(string)$this->pageImportCount,
			strlen( (string)$this->totalPagesBeingImported ),
			' ',
			STR_PAD_LEFT
		);

		$progressCounter = "($currentProgress/$this->totalPagesBeingImported)";
		$this->output( "$progressCounter {$foreignTitle->getFullText()} --> {$title->getFullText()}\n" );
	}

	private function getPathOfFileToImport(): string {
		$xmlPath = $this->getArg( 'xml-path' );
		if ( !file_exists( $xmlPath ) ) {
			$this->fatalError( "File '$xmlPath' does not exist" );
		}

		if ( !is_readable( $xmlPath ) ) {
			$this->fatalError( "File '$xmlPath' is not readable" );
		}

		return $xmlPath;
	}

	private function getImportUser(): UserIdentity {
		$username = $this->getOption( 'user' );

		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		$user = $userFactory->newFromName( $username );

		if ( $user === null || !$user->isNamed() ) {
			$this->fatalError( "User $username does not exist." );
		}

		return $user;
	}

	private function getInterwikiPrefix(): string {
		$interwikiPrefix = trim( $this->getOption( 'interwiki-prefix', '' ) );
		if ( $interwikiPrefix === '' ) {
			$this->fatalError( 'Argument interwiki-prefix cannot be empty.' );
		}

		return $interwikiPrefix;
	}

	private function getPriorityLanguages(): array {
		$priorityLanguageCodes = self::commaList2Array( $this->getOption( 'priority-languages' ) ?? '' );
		$knownLanguageCodes = array_keys( Utilities::getLanguageNames( 'en' ) );
		$invalidLanguageCodes = array_diff( $priorityLanguageCodes, $knownLanguageCodes );

		if ( $invalidLanguageCodes ) {
			$this->fatalError(
				'Unknown priority language code(s): ' . implode( ', ', $invalidLanguageCodes )
			);
		}

		return $priorityLanguageCodes;
	}

	private function markPageForTranslation(
		Title $bundleTitle,
		TranslatablePageSettings $translatablePageSettings,
		UserIdentity $importUser
	): void {
		$translatablePageMarker = Services::getInstance()->getTranslatablePageMarker();
		$user = MediaWikiServices::getInstance()->getUserFactory()->newFromUserIdentity( $importUser );
		try {
			$operation = $translatablePageMarker->getMarkOperation(
				$bundleTitle->toPageRecord( IDBAccessObject::READ_LATEST ),
				null,
				$translatablePageSettings->shouldTranslateTitle()
			);
		} catch ( TranslatablePageMarkException $e ) {
			$this->error( "Error while marking page {$bundleTitle->getPrefixedText()} for translation.\n" );
			$this->error( "Fix the issues and mark the page for translation using Special:PageTranslation.\n\n" );
			$this->fatalError( wfMessage( $e->getMessageObject() )->text() );
		}

		$statusFormatter = MediaWikiServices::getInstance()
			->getFormatterFactory()
			->getStatusFormatter( RequestContext::getMain() );

		$unitNameValidationResult = $operation->getUnitValidationStatus();
		if ( !$unitNameValidationResult->isOK() ) {
			$this->output( "Unit validation failed for {$bundleTitle->getPrefixedText()}.\n" );
			$this->fatalError( $statusFormatter->getMessage( $unitNameValidationResult )->text() );
		}

		try {
			$translatablePageMarker->markForTranslation(
				$operation,
				$translatablePageSettings,
				RequestContext::getMain(),
				$user
			);
			$this->output( "The page {$bundleTitle->getPrefixedText()} has been marked for translation.\n" );
		} catch ( TranslatablePageMarkException $e ) {
			$this->error( "Error while marking page {$bundleTitle->getPrefixedText()} for translation.\n" );
			$this->error( "Fix the issues and mark the page for translation using Special:PageTranslation.\n\n" );
			$this->fatalError( $e->getMessageObject()->text() );
		}
	}

	private function getTranslatablePageSettings(): TranslatablePageSettings {
		return new TranslatablePageSettings(
			$this->getPriorityLanguages(),
			$this->hasOption( 'force-priority-languages' ),
			$this->getOption( 'priority-languages-reason' ) ?? '',
			[],
			!$this->hasOption( 'skip-translating-title' ),
			!$this->hasOption( 'use-old-syntax-version' ),
			!$this->hasOption( 'disallow-transclusion' ),
		);
	}

	private function getTargetPageName(): ?Title {
		$targetPage = $this->getOption( 'target-name' );
		if ( $targetPage === null ) {
			return null;
		}

		try {
			$targetPageTitle = MediaWikiServices::getInstance()->getTitleFactory()->newFromTextThrow( $targetPage );
		} catch ( MalformedTitleException $e ) {
			$this->fatalError(
				"Target page name $targetPage does not appear to be valid: {$e->getMessage()}"
			);
		}

		$shouldOverride = $this->hasOption( 'override' );
		if ( $targetPageTitle->exists() && !$shouldOverride ) {
			$this->fatalError(
				"Specified target page $targetPage already exists. Use '--override' if you still want to import"
			);
		}

		if ( !$targetPageTitle->canExist() ) {
			$this->fatalError( "The target page name $targetPage cannot be created" );
		}

		return $targetPageTitle;
	}
}
