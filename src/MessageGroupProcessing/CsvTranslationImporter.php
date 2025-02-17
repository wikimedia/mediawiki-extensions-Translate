<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Permissions\Authority;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Status\Status;
use MediaWiki\Title\Title;
use SplFileObject;

/**
 * Parse, validate and import translations from a CSV file
 * @since 2022.06
 * @license GPL-2.0-or-later
 * @author Abijeet Patro
 */
class CsvTranslationImporter {
	private WikiPageFactory $wikiPageFactory;

	public function __construct( WikiPageFactory $wikiPageFactory ) {
		$this->wikiPageFactory = $wikiPageFactory;
	}

	/** Parse and validate the CSV file */
	public function parseFile( string $csvFilePath ): Status {
		if ( !file_exists( $csvFilePath ) || !is_file( $csvFilePath ) ) {
			return Status::newFatal(
				"CSV file path '$csvFilePath' does not exist, is not readable or is not a file"
			);
		}

		$indexedLanguageCodes = [];
		$currentRowCount = -1;
		$importData = [];
		$invalidRows = [
			'emptyTitleRows' => [],
			'invalidTitleRows' => [],
			'groupNotFoundRows' => []
		];

		$csvFileContent = new SplFileObject( $csvFilePath, 'r' );
		while ( !$csvFileContent->eof() ) {
			// Increment the row count at the beginning since we have a bunch of jump statements
			// at various placaes
			++$currentRowCount;

			$csvRow = $csvFileContent->fgetcsv( ',', '"', "\\" );
			if ( $this->isCsvRowEmpty( $csvRow ) ) {
				continue;
			}

			if ( $currentRowCount === 0 ) {
				// Validate the header
				$status = $this->getLanguagesFromHeader( $csvRow );
				if ( !$status->isGood() ) {
					return $status;
				}
				/** @var string[] $indexedLanguageCodes */
				$indexedLanguageCodes = $status->getValue();
				continue;
			}

			$rowData = [ 'translations' => [] ];
			$messageTitle = isset( $csvRow[0] ) ? trim( $csvRow[0] ) : null;
			if ( !$messageTitle ) {
				$invalidRows['emptyTitleRows'][] = $currentRowCount + 1;
				continue;
			}

			$handle = $this->getMessageHandleIfValid( $messageTitle );
			if ( $handle === null ) {
				$invalidRows['invalidTitleRows'][] = $currentRowCount + 1;
				continue;
			}

			// Ensure that the group is present
			$group = $handle->getGroup();
			if ( !$group ) {
				$invalidRows['groupNotFoundRows'][] = $currentRowCount + 1;
				continue;
			}

			$sourceLanguage = $group->getSourceLanguage();

			$rowData['messageTitle'] = $messageTitle;
			foreach ( $indexedLanguageCodes as $languageCode => $index ) {
				if ( $sourceLanguage === $languageCode ) {
					continue;
				}

				$rowData['translations'][$languageCode] = $csvRow[$index] ?? null;
			}
			$importData[] = $rowData;
		}

		$status = new Status();
		if ( $invalidRows['emptyTitleRows'] ) {
			$status->fatal(
				'Empty message titles found on row(s): ' . implode( ',', $invalidRows['emptyTitleRows'] )
			);
		}

		if ( $invalidRows['invalidTitleRows'] ) {
			$status->fatal(
				'Invalid message title(s) found on row(s): ' . implode( ',', $invalidRows['invalidTitleRows'] )
			);
		}

		if ( $invalidRows['groupNotFoundRows'] ) {
			$status->fatal(
				'Group not found for message(s) on row(s) ' . implode( ',', $invalidRows['invalidTitleRows'] )
			);
		}

		if ( !$status->isGood() ) {
			return $status;
		}

		return Status::newGood( $importData );
	}

	/** Import the data returned from the parseFile method */
	public function importData(
		array $messagesWithTranslations,
		Authority $authority,
		string $comment,
		?callable $progressReporter = null
	): Status {
		$commentStoreComment = CommentStoreComment::newUnsavedComment( $comment );

		// Loop over each translation to import
		$importStatus = new Status();
		$failedStatuses = [];
		$currentTranslation = 0;
		foreach ( $messagesWithTranslations as $messageTranslation ) {
			$messageTitleText = $messageTranslation['messageTitle'];
			$messageTitle = Title::newFromText( $messageTitleText );
			$messageHandle = new MessageHandle( $messageTitle );

			$translationImportStatuses = [];

			// Import each translation for the current message
			$translations = $messageTranslation['translations'];
			foreach ( $translations as $languageCode => $translation ) {
				// Skip empty translations
				if ( $translation === null || trim( $translation ) === '' ) {
					continue;
				}

				$translationTitle = $messageHandle->getTitleForLanguage( $languageCode );

				// Perform the update for the translation page
				$updater = $this->wikiPageFactory->newFromTitle( $translationTitle )
					->newPageUpdater( $authority );
				$content = ContentHandler::makeContent( $translation, $translationTitle );
				$updater->setContent( SlotRecord::MAIN, $content );
				$updater->setFlags( EDIT_FORCE_BOT );
				$updater->saveRevision( $commentStoreComment );

				$status = $updater->getStatus();
				$translationImportStatuses[] = $status;
				if ( !$status->isOK() ) {
					$failedStatuses[ $translationTitle->getPrefixedText() ] = $status;
				}
			}

			++$currentTranslation;
			if ( $progressReporter ) {
				$progressReporter(
					$messageTitle,
					$translationImportStatuses,
					count( $messagesWithTranslations ),
					$currentTranslation
				);
			}
		}

		if ( $failedStatuses ) {
			foreach ( $failedStatuses as $failedStatus ) {
				$importStatus->merge( $failedStatus );
			}

			$importStatus->setResult( false, $failedStatuses );
		}

		return $importStatus;
	}

	private function getLanguagesFromHeader( array $csvHeader ): Status {
		if ( count( $csvHeader ) < 2 ) {
			return Status::newFatal(
				'CSV has < 2 columns. Assuming that there are no languages to import'
			);
		}

		$languageCodesInHeader = array_slice( $csvHeader, 2 );
		if ( $languageCodesInHeader === [] ) {
			return Status::newFatal( 'No languages found for import' );
		}

		$invalidLanguageCodes = [];
		$indexedLanguageCodes = [];
		// First two columns are message title and definition
		$originalLanguageIndex = 2;
		foreach ( $languageCodesInHeader as $languageCode ) {
			if ( !Utilities::isSupportedLanguageCode( strtolower( $languageCode ) ) ) {
				$invalidLanguageCodes[] = $languageCode;
			} else {
				// Language codes maybe in upper case, convert to lower case for further use.
				$indexedLanguageCodes[ strtolower( $languageCode ) ] = $originalLanguageIndex;
			}
			++$originalLanguageIndex;
		}

		if ( $invalidLanguageCodes ) {
			return Status::newFatal(
				'Invalid language codes detected in CSV header: ' . implode( ', ', $invalidLanguageCodes )
			);
		}

		return Status::newGood( $indexedLanguageCodes );
	}

	private function getMessageHandleIfValid( string $messageTitle ): ?MessageHandle {
		$title = Title::newFromText( $messageTitle );
		if ( $title === null ) {
			return null;
		}

		$handle = new MessageHandle( $title );
		if ( $handle->isValid() ) {
			return $handle;
		}

		return null;
	}

	private function isCsvRowEmpty( array $csvRow ): bool {
		return count( $csvRow ) === 1 && ( $csvRow[0] === null || trim( $csvRow[0] ) === '' );
	}
}
