<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use FileBasedMessageGroup;
use GettextFFS;
use Html;
use HTMLForm;
use LogicException;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\MediaWikiServices;
use Message;
use MessageCollection;
use MessageGroup;
use MessageGroups;
use MessageHandle;
use SpecialPage;
use Status;
use Title;
use TranslateUtils;
use WikiPageMessageGroup;

/**
 * This special page allows exporting groups for offline translation.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage TranslateSpecialPage
 */
class ExportTranslationsSpecialPage extends SpecialPage {
	/** Maximum size of a group until exporting is not allowed due to performance reasons. */
	public const MAX_EXPORT_SIZE = 10000;
	/** @var string */
	protected $language;
	/** @var string */
	protected $format;
	/** @var string */
	protected $groupId;
	/** @var string[] */
	private const VALID_FORMATS = [ 'export-as-po', 'export-to-file', 'export-as-csv' ];

	public function __construct() {
		parent::__construct( 'ExportTranslations' );
	}

	/** @param null|string $par */
	public function execute( $par ) {
		$out = $this->getOutput();
		$request = $this->getRequest();
		$lang = $this->getLanguage();

		$this->setHeaders();

		$this->groupId = $request->getText( 'group', $par ?? '' );
		$this->language = $request->getVal( 'language', $lang->getCode() );
		$this->format = $request->getText( 'format' );

		$this->outputForm();

		if ( $this->groupId ) {
			$status = $this->checkInput();
			if ( !$status->isGood() ) {
				$out->wrapWikiTextAsInterface(
					'error',
					$status->getWikiText( false, false, $lang )
				);
				return;
			}

			$this->doExport();
		}
	}

	private function outputForm(): void {
		$fields = [
			'group' => [
				'type' => 'select',
				'name' => 'group',
				'id' => 'group',
				'label-message' => 'translate-page-group',
				'options' => $this->getGroupOptions(),
				'default' => $this->groupId,
			],
			'language' => [
				// @todo Apply ULS to this field
				'type' => 'select',
				'name' => 'language',
				'id' => 'language',
				'label-message' => 'translate-page-language',
				'options' => $this->getLanguageOptions(),
				'default' => $this->language,
			],
			'format' => [
				'type' => 'radio',
				'name' => 'format',
				'id' => 'format',
				'label-message' => 'translate-export-form-format',
				'flatlist' => true,
				'options' => $this->getFormatOptions(),
				'default' => $this->format,
			],
		];
		HTMLForm::factory( 'ooui', $fields, $this->getContext() )
			->setMethod( 'get' )
			->setWrapperLegendMsg( 'translate-page-settings-legend' )
			->setSubmitTextMsg( 'translate-submit' )
			->prepareForm()
			->displayForm( false );
	}

	private function getGroupOptions(): array {
		$selected = $this->groupId;
		$groups = MessageGroups::getAllGroups();
		uasort( $groups, [ MessageGroups::class, 'groupLabelSort' ] );

		$options = [];
		foreach ( $groups as $id => $group ) {
			if ( !$group->exists()
				|| ( MessageGroups::getPriority( $group ) === 'discouraged' && $id !== $selected )
			) {
				continue;
			}

			$options[$group->getLabel()] = $id;
		}

		return $options;
	}

	/** @return string[] */
	private function getLanguageOptions(): array {
		$languages = TranslateUtils::getLanguageNames( 'en' );
		$options = [];
		foreach ( $languages as $code => $name ) {
			$options["$code - $name"] = $code;
		}

		return $options;
	}

	/** @return string[] */
	private function getFormatOptions(): array {
		$options = [];
		foreach ( self::VALID_FORMATS as $format ) {
			// translate-taskui-export-to-file, translate-taskui-export-as-po
			$options[ $this->msg( "translate-taskui-$format" )->escaped() ] = $format;
		}
		return $options;
	}

	private function checkInput(): Status {
		$status = Status::newGood();

		$msgGroup = MessageGroups::getGroup( $this->groupId );
		if ( $msgGroup === null ) {
			$status->fatal( 'translate-page-no-such-group' );
		} elseif ( MessageGroups::isDynamic( $msgGroup ) ) {
			$status->fatal( 'translate-export-not-supported' );
		}

		$langNames = TranslateUtils::getLanguageNames( 'en' );
		if ( !isset( $langNames[$this->language] ) ) {
			$status->fatal( 'translate-page-no-such-language' );
		}

		// Do not show this error if invalid format is specified for translatable page
		// groups as we can show a textarea box containing the translation page text
		// (however it's not currently supported for other groups).
		if (
			!$msgGroup instanceof WikiPageMessageGroup
			&& $this->format
			&& !in_array( $this->format, self::VALID_FORMATS )
		) {
			$status->fatal( 'translate-export-invalid-format' );
		}

		if ( $this->format === 'export-to-file'
			&& !$msgGroup instanceof FileBasedMessageGroup
		) {
			$status->fatal( 'translate-export-format-notsupported' );
		}

		if ( $msgGroup && !MessageGroups::isDynamic( $msgGroup ) ) {
			$size = count( $msgGroup->getKeys() );
			if ( $size > self::MAX_EXPORT_SIZE ) {
				$status->fatal(
					'translate-export-group-too-large',
					Message::numParam( self::MAX_EXPORT_SIZE )
				);
			}
		}

		return $status;
	}

	private function doExport(): void {
		$out = $this->getOutput();
		$group = MessageGroups::getGroup( $this->groupId );
		$collection = $this->setupCollection( $group );

		switch ( $this->format ) {
			case 'export-as-po':
				$out->disable();

				$ffs = null;
				if ( $group instanceof FileBasedMessageGroup ) {
					$ffs = $group->getFFS();
				}

				if ( !$ffs instanceof GettextFFS ) {
					if ( !$group instanceof FileBasedMessageGroup ) {
						$group = FileBasedMessageGroup::newFromMessageGroup( $group );
					}

					$ffs = new GettextFFS( $group );
				}

				$ffs->setOfflineMode( true );

				$filename = "{$group->getId()}_{$this->language}.po";
				$this->sendExportHeaders( $filename );

				echo $ffs->writeIntoVariable( $collection );
				break;

			case 'export-to-file':
				$out->disable();

				// This will never happen since its checked previously but add the check to keep
				// phan and IDE happy. See checkInput method
				if ( !$group instanceof FileBasedMessageGroup ) {
					throw new LogicException(
						"'export-to-file' requested for a non FileBasedMessageGroup {$group->getId()}"
					);
				}

				$filename = basename( $group->getSourceFilePath( $collection->getLanguage() ) );
				$this->sendExportHeaders( $filename );

				echo $group->getFFS()->writeIntoVariable( $collection );
				break;

			case 'export-as-csv':
				$out->disable();
				$filename = "{$group->getId()}_{$this->language}.csv";
				$this->sendExportHeaders( $filename );
				$this->exportCSV( $collection, $group->getSourceLanguage() );
				break;

			default:
				// @todo Add web viewing for groups other than WikiPageMessageGroup
				if ( !$group instanceof WikiPageMessageGroup ) {
					return;
				}

				$translatablePage = TranslatablePage::newFromTitle( $group->getTitle() );
				$translationPage = $translatablePage->getTranslationPage( $collection->getLanguage() );

				$translationPage->filterMessageCollection( $collection );
				$messages = $translationPage->extractMessages( $collection );
				$text = $translationPage->generateSourceFromTranslations( $messages );

				$displayTitle = $translatablePage->getPageDisplayTitle( $this->language );
				if ( $displayTitle ) {
					$text = "{{DISPLAYTITLE:$displayTitle}}$text";
				}

				$box = Html::element(
					'textarea',
					[ 'id' => 'wpTextbox', 'rows' => 40, ],
					$text
				);
				$out->addHTML( $box );

		}
	}

	private function setupCollection( MessageGroup $group ): MessageCollection {
		$collection = $group->initCollection( $this->language );

		// Don't export ignored, unless it is the source language or message documentation
		$translateDocCode = $this->getConfig()->get( 'TranslateDocumentationLanguageCode' );
		if ( $this->language !== $translateDocCode
			&& $this->language !== $group->getSourceLanguage()
		) {
			$collection->filter( 'ignored' );
		}

		$collection->loadTranslations();

		return $collection;
	}

	/** Send the appropriate response headers for the export */
	private function sendExportHeaders( string $fileName ): void {
		$response = $this->getRequest()->response();
		$response->header( 'Content-Type: text/plain; charset=UTF-8' );
		$response->header( "Content-Disposition: attachment; filename=\"$fileName\"" );
	}

	private function exportCSV( MessageCollection $collection, string $sourceLanguageCode ): void {
		$fp = fopen( 'php://output', 'w' );
		$exportingSourceLanguage = $sourceLanguageCode === $this->language;

		$header = [
			$this->msg( 'translate-export-csv-message-title' )->text(),
			$this->msg( 'translate-export-csv-definition' )->text()
		];

		if ( !$exportingSourceLanguage ) {
			$header[] = $this->language;
		}

		fputcsv( $fp, $header );

		$titleFormatter = MediaWikiServices::getInstance()->getTitleFormatter();

		foreach ( $collection->keys() as $messageKey => $titleValue ) {
			$message = $collection[ $messageKey ];
			$prefixedTitleText = $titleFormatter->getPrefixedText( $titleValue );

			$handle = new MessageHandle( Title::newFromText( $prefixedTitleText ) );
			$sourceLanguageTitle = $handle->getTitleForLanguage( $sourceLanguageCode );

			$row = [ $sourceLanguageTitle->getPrefixedText(), $message->definition() ];

			if ( !$exportingSourceLanguage ) {
				$row[] = $message->translation();
			}

			fputcsv( $fp, $row );
		}

		fclose( $fp );
	}

	protected function getGroupName() {
		return 'translation';
	}
}
