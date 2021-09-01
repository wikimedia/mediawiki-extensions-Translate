<?php

/**
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialExportTranslations extends SpecialPage {
	/**
	 * Maximum size of a group until exporting is not allowed due to performance reasons.
	 */
	public const MAX_EXPORT_SIZE = 10000;

	/** @var string */
	protected $language;
	/** @var string */
	protected $format;
	/** @var string */
	protected $groupId;
	/** @var string[] */
	public static $validFormats = [ 'export-as-po', 'export-to-file' ];

	public function __construct() {
		parent::__construct( 'ExportTranslations' );
	}

	/** @param null|string $par */
	public function execute( $par ) {
		$out = $this->getOutput();
		$request = $this->getRequest();
		$lang = $this->getLanguage();

		$this->setHeaders();

		$this->groupId = $request->getText( 'group', $par );
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

	protected function outputForm() {
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

	/** @return array */
	protected function getGroupOptions() {
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

	/** @return array */
	protected function getLanguageOptions() {
		$languages = TranslateUtils::getLanguageNames( 'en' );
		$options = [];
		foreach ( $languages as $code => $name ) {
			$options["$code - $name"] = $code;
		}

		return $options;
	}

	/** @return array */
	protected function getFormatOptions() {
		$options = [];
		foreach ( self::$validFormats as $format ) {
			// translate-taskui-export-to-file, translate-taskui-export-as-po
			$options[ $this->msg( "translate-taskui-$format" )->escaped() ] = $format;
		}
		return $options;
	}

	/** @return Status */
	protected function checkInput() {
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

		// Do not show this error if no/invalid format is specified for translatable
		// page groups as we can show a textarea box containing the translation page text
		// (however it's not currently supported for other groups).
		if (
			!$msgGroup instanceof WikiPageMessageGroup
			&& !in_array( $this->format, self::$validFormats )
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

	protected function doExport() {
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

				'@phan-var FileBasedMessageGroup $group';
				$filename = basename( $group->getSourceFilePath( $collection->getLanguage() ) );
				$this->sendExportHeaders( $filename );

				echo $group->getFFS()->writeIntoVariable( $collection );
				break;

			default:
				// @todo Add web viewing for groups other than WikiPageMessageGroup
				if ( !$group instanceof WikiPageMessageGroup ) {
					// This should have been prevented at validation. See checkInput().
					throw new LogicException( 'Unexpected export format.' );
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

	private function setupCollection( MessageGroup $group ) {
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

	/**
	 * Send the appropriate response headers for the export
	 *
	 * @param string $fileName
	 */
	protected function sendExportHeaders( $fileName ) {
		$response = $this->getRequest()->response();
		$response->header( 'Content-Type: text/plain; charset=UTF-8' );
		$response->header( "Content-Disposition: attachment; filename=\"$fileName\"" );
	}

	protected function getGroupName() {
		return 'translation';
	}
}
