<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

/**
 * Script to import translations from a CSV file
 * @since 2022.06
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class ImportTranslationsFromCsv extends BaseMaintenanceScript {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Import translations for a CSV file' );

		$this->addArg(
			'file',
			'Path to CSV file to import',
			self::REQUIRED
		);

		$this->addOption(
			'summary',
			'The change summary when updating the translations',
			self::REQUIRED,
			self::HAS_ARG
		);

		$this->addOption(
			'user',
			'User ID of the user performing the import',
			self::REQUIRED,
			self::HAS_ARG
		);

		$this->addOption(
			'really',
			'Should the import actually be performed',
			self::OPTIONAL,
			self::NO_ARG
		);

		$this->requireExtension( 'Translate' );
	}

	/** @inheritDoc */
	public function execute() {
		$csvFilePath = $this->getArg( 0 );

		$username = $this->getOption( 'user' );
		$summary = $this->getOption( 'summary' );

		// Validate the parameters
		if ( trim( $summary ) === '' ) {
			$this->fatalError( 'Please provide a non-empty "summary"' );
		}

		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		$user = $userFactory->newFromName( $username );

		if ( $user === null || !$user->isRegistered() ) {
			$this->fatalError( "User $username does not exist." );
		}

		// Validate and parse the CSV file
		$csvImporter = Services::getInstance()->getCsvTranslationImporter();
		$status = $csvImporter->parseFile( $csvFilePath );

		if ( $status->isOK() ) {
			$messagesWithTranslations = $status->getValue();
			$output = "\n";
			foreach ( $messagesWithTranslations as $messageTranslations ) {
				$translations = $messageTranslations[ 'translations' ];
				$output .= '* ' . count( $this->filterEmptyTranslations( $translations ) ) .
					' translation(s) to import for ' .
					$messageTranslations['messageTitle'] . "\n";
			}

			$this->output( $output . "\n" );
		} else {
			$this->error( "Error during processing:\n" );
			$this->error( (string)$status );
			$this->fatalError( 'Exiting...' );
		}

		if ( !$this->hasOption( 'really' ) ) {
			$this->output( "\nUse option --really to perform the import.\n" );
			return true;
		}

		// Start the actual import of translations
		$this->output( "\nProceeding with import...\n\n" );
		$importStatus = $csvImporter->importData(
			$status->getValue(), $user, trim( $summary ), [ $this, 'progressReporter' ]
		);

		if ( $importStatus->isOK() ) {
			$this->output( "\nSuccess: Import done\n" );
		} else {
			$this->output( "\nImport failed. See errors:\n" );
			$failedImportStatuses = $importStatus->getValue();
			foreach ( $failedImportStatuses as $translationTitleText => $status ) {
				$this->output( "\nImport failed for $translationTitleText:\n" );
				$this->output( $status );
			}

			return false;
		}

		return true;
	}

	public function progressReporter(
		Title $title,
		array $messageImportStatuses,
		int $total,
		int $processed
	): void {
		$paddedProcessed = str_pad( (string)$processed, strlen( (string)$total ), ' ', STR_PAD_LEFT );
		$progressCounter = "($paddedProcessed/$total)";

		$successCount = 0;
		$failCount = 0;
		foreach ( $messageImportStatuses as $messageImportStatus ) {
			if ( $messageImportStatus->isOK() ) {
				$successCount++;
			} else {
				$failCount++;
			}
		}

		$this->output(
			"$progressCounter Imported translations for {$title->getPrefixedText()} with $failCount " .
			"failure(s) and $successCount successful import(s) ...\n"
		);
	}

	private function filterEmptyTranslations( array $translations ): array {
		return array_filter( $translations, 'strlen' );
	}
}
