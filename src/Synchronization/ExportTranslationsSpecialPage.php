<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Synchronization;

use FileBasedMessageGroup;
use LogicException;
use MediaWiki\Extension\Translate\FileFormatSupport\GettextFormat;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePage;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Html\Html;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\Language\FormatterFactory;
use MediaWiki\Message\Message;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Status\Status;
use MediaWiki\Status\StatusFormatter;
use MediaWiki\Title\Title;
use MediaWiki\Title\TitleFormatter;
use MessageGroup;
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
	protected string $language;
	protected string $format;
	protected string $groupId;
	private TitleFormatter $titleFormatter;
	private ParserFactory $parserFactory;
	private StatusFormatter $statusFormatter;
	/** @var string[] */
	private const VALID_FORMATS = [ 'export-as-po', 'export-to-file', 'export-as-csv' ];

	public function __construct(
		TitleFormatter $titleFormatter,
		ParserFactory $parserFactory,
		FormatterFactory $formatterFactory
	) {
		parent::__construct( 'ExportTranslations' );
		$this->titleFormatter = $titleFormatter;
		$this->parserFactory = $parserFactory;
		$this->statusFormatter = $formatterFactory->getStatusFormatter( $this );
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
		$out->addModules( 'ext.translate.special.exporttranslations' );

		if ( $this->groupId ) {
			$status = $this->checkInput();
			if ( !$status->isGood() ) {
				$out->wrapWikiTextAsInterface(
					'error',
					$this->statusFormatter->getWikiText( $status )
				);
				return;
			}

			$status = $this->doExport();
			if ( !$status->isGood() ) {
				$out->addHTML(
					Html::errorBox( $this->statusFormatter->getHTML( $status, [ 'lang' => $lang ] ) )
				);
			}
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
			->setId( 'mw-export-message-group-form' )
			->setWrapperLegendMsg( 'translate-page-settings-legend' )
			->setSubmitTextMsg( 'translate-submit' )
			->prepareForm()
			->displayForm( false );
	}

	private function getGroupOptions(): array {
		$groups = MessageGroups::getAllGroups();
		uasort( $groups, [ MessageGroups::class, 'groupLabelSort' ] );

		$options = [];
		foreach ( $groups as $id => $group ) {
			if ( !$group->exists() ) {
				continue;
			}

			$options[$group->getLabel()] = $id;
		}

		return $options;
	}

	/** @return string[] */
	private function getLanguageOptions(): array {
		$languages = Utilities::getLanguageNames( 'en' );
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

		$langNames = Utilities::getLanguageNames( 'en' );
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

	private function doExport(): Status {
		$out = $this->getOutput();
		$group = MessageGroups::getGroup( $this->groupId );
		$collection = $this->setupCollection( $group );

		switch ( $this->format ) {
			case 'export-as-po':
				$out->disable();

				$fileFormat = null;
				if ( $group instanceof FileBasedMessageGroup ) {
					$fileFormat = $group->getFFS();
				}

				if ( !$fileFormat instanceof GettextFormat ) {
					if ( !$group instanceof FileBasedMessageGroup ) {
						$group = FileBasedMessageGroup::newFromMessageGroup( $group );
					}

					$fileFormat = new GettextFormat( $group );
				}

				$fileFormat->setOfflineMode( true );

				$filename = "{$group->getId()}_{$this->language}.po";
				$this->sendExportHeaders( $filename );

				echo $fileFormat->writeIntoVariable( $collection );
				break;

			case 'export-to-file':
				// This will never happen since its checked previously but add the check to keep
				// phan and IDE happy. See checkInput method
				if ( !$group instanceof FileBasedMessageGroup ) {
					throw new LogicException(
						"'export-to-file' requested for a non FileBasedMessageGroup {$group->getId()}"
					);
				}

				$messages = $group->getFFS()->writeIntoVariable( $collection );

				if ( $messages === '' ) {
					return Status::newFatal( 'translate-export-format-file-empty' );
				}

				$out->disable();
				$filename = basename( $group->getSourceFilePath( $collection->getLanguage() ) );
				$this->sendExportHeaders( $filename );
				echo $messages;
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
					return Status::newFatal( 'translate-export-format-notsupported' );
				}

				$translatablePage = TranslatablePage::newFromTitle( $group->getTitle() );
				$translationPage = $translatablePage->getTranslationPage( $collection->getLanguage() );

				$translationPage->filterMessageCollection( $collection );
				$text = $translationPage->generateSourceFromMessageCollection(
					$this->parserFactory->getInstance(),
					$collection
				);

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

		return Status::newGood();
	}

	private function setupCollection( MessageGroup $group ): MessageCollection {
		$collection = $group->initCollection( $this->language );

		// Don't export ignored, unless it is the source language or message documentation
		$translateDocCode = $this->getConfig()->get( 'TranslateDocumentationLanguageCode' );
		if ( $this->language !== $translateDocCode
			&& $this->language !== $group->getSourceLanguage()
		) {
			$collection->filter( MessageCollection::FILTER_IGNORED, MessageCollection::EXCLUDE_MATCHING );
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

		fputcsv( $fp, $header, ',', '"', "\\" );

		foreach ( $collection->keys() as $messageKey => $titleValue ) {
			$message = $collection[ $messageKey ];
			$prefixedTitleText = $this->titleFormatter->getPrefixedText( $titleValue );

			$handle = new MessageHandle( Title::newFromText( $prefixedTitleText ) );
			$sourceLanguageTitle = $handle->getTitleForLanguage( $sourceLanguageCode );

			$row = [ $sourceLanguageTitle->getPrefixedText(), $message->definition() ];

			if ( !$exportingSourceLanguage ) {
				$row[] = $message->translation();
			}

			fputcsv( $fp, $row, ',', '"', "\\" );
		}

		fclose( $fp );
	}

	protected function getGroupName(): string {
		return 'translation';
	}
}
