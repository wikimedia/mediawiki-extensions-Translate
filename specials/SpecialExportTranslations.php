<?php
/**
 * @license GPL-2.0+
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialExportTranslations extends SpecialPage {
	/** @var string */
	protected $language;

	/** @var string */
	protected $format;

	/** @var string */
	protected $group;

	/** @var string[] */
	public static $validFormats = array( 'export-as-po', 'export-to-file' );

	public function __construct() {
		parent::__construct( 'ExportTranslations' );
	}

	/**
	 * @param null|string $par
	 */
	public function execute( $par ) {
		$out = $this->getOutput();
		$request = $this->getRequest();
		$lang = $this->getLanguage();

		$this->setHeaders();

		$this->group = $request->getText( 'group', $par );
		$this->language = $request->getVal( 'language', $lang->getCode() );
		$this->format = $request->getText( 'format' );

		$this->outputForm();

		if ( $this->group ) {
			$status = $this->checkInput();
			if ( !$status->isGood() ) {
				$errors = $out->parse( $status->getWikiText( false, false, $lang ) );
				$out->addHTML( Html::rawElement( 'div', array( 'class' => 'error' ), $errors ) );
				return;
			}

			$this->doExport();

		/**
		  Figure out something for
			 ExportMessagesTask
			 ExportAsPoMessagesTask
			 ExportToFileMessagesTask
			 PageTranslationHooks::sourceExport
			 others??
		 */
		}

	}

	protected function outputForm() {
		$fields = array(
			'group' => array(
				'type' => 'select',
				'name' => 'group',
				'id' => 'group',
				'label-message' => 'translate-page-group',
				'options' => $this->getGroupOptions(),
				'default' => $this->group,
			),
			'language' => array(
				'type' => 'select',
				'name' => 'language',
				'id' => 'language',
				'label-message' => 'translate-page-language',
				'options' => $this->getLanguageOptions(),
				'default' => $this->language,
			),
			'format' => array(
				'type' => 'radio',
				'name' => 'format',
				'id' => 'format',
				'label-message' => 'translate-export-form-format',
				'flatlist' => true,
				'options' => $this->getFormatOptions(),
				'default' => $this->format,
			),
		);
		$form = HTMLForm::factory( 'table', $fields, $this->getContext() );
		$form
			->setMethod( 'get' )
			->setWrapperLegendMsg( 'translate-page-settings-legend' )
			->setSubmitTextMsg( 'translate-submit' )
			->prepareForm()
			->displayForm( false );
	}

	/**
	 * @return array
	 */
	protected function getGroupOptions() {
		$selected = $this->group;
		$groups = MessageGroups::getAllGroups();
		uasort( $groups, array( 'MessageGroups', 'groupLabelSort' ) );

		$options = array();
		foreach ( $groups as $id => $group ) {
			if ( !$group->exists()
				|| ( MessageGroups::getPriority( $group ) === 'discouraged' && $id !== $selected )
			) {
				continue;
			}

			$options[ $group->getLabel() ] = $id;
		}

		return $options;
	}

	/**
	 * @return array
	 */
	protected function getLanguageOptions() {
		$languages = TranslateUtils::getLanguageNames( 'en' );
		$options = array();
		foreach ( $languages as $code => $name ) {
			$options["$code - $name"] = $code;
		}

		return $options;
	}

	/**
	 * @return array
	 */
	protected function getFormatOptions() {
		$options = array();
		foreach ( self::$validFormats as $format ) {
			// translate-taskui-export-to-file, translate-taskui-export-as-po
			$options[ $this->msg( "translate-taskui-$format" )->escaped() ] = $format;
		}
		return $options;
	}

	/**
	 * @return Status
	 */
	protected function checkInput() {
		$status = Status::newGood();

		$msgGroup = MessageGroups::getGroup( $this->group );
		if ( $msgGroup === null ) {
			$status->fatal( 'translate-page-no-such-group' );
		} elseif ( MessageGroups::isDynamic( $msgGroup ) ) {
			$status->fatal( 'translate-export-not-supported' );
		}

		$langNames = TranslateUtils::getLanguageNames( 'en' );
		if ( !isset( $langNames[$this->language] ) ) {
			$status->fatal( 'translate-page-no-such-language' );
		} elseif ( $msgGroup && $msgGroup->getSourceLanguage() === $this->language ) {
			$langName = TranslateUtils::getLanguageName(
				$this->language,
				$this->getLanguage()->getCode()
			);
			$status->fatal( 'translate-page-disabled-source', $langName );
		}

		// We can show the translation text for translatable pages only currently
		if ( !$msgGroup instanceof WikiPageMessageGroup
			&& !in_array( $this->format, self::$validFormats )
		) {
			$status->fatal( 'translate-export-invalid-format' );
		}

		return $status;
	}

	protected function doExport() {
		$out = $this->getOutput();
		$group = MessageGroups::getGroup( $this->group );
		$collection = $this->setupCollection( $group );

		switch ( $this->format ) {
			case 'export-as-po':
				$out->disable();

				$ffs = null;
				if ( $group instanceof FileBasedMessageGroup ) {
					$ffs = $group->getFFS();
				}

				if ( !$ffs instanceof GettextFFS ) {
					$group = FileBasedMessageGroup::newFromMessageGroup( $group );
					$ffs = new GettextFFS( $group );
				}

				$ffs->setOfflineMode( true );

				$filename = "{$group->getID()}_{$this->language}.po";
				header( "Content-Disposition: attachment; filename=\"$filename\"" );

				echo $ffs->writeIntoVariable( $collection );
				break;

			case 'export-to-file':
				$out->disable();

				if ( !$group instanceof FileBasedMessageGroup ) {
					// @todo Move to checkInput()
					echo 'Not supported';
					return;
				}

				$data = $group->getFFS() > writeIntoVariable( $collection );
				$filename = basename( $group->getSourceFilePath( $collection->getLanguage() ) );
				header( "Content-Disposition: attachment; filename=\"$filename\"" );

				echo $data;
				break;

			default:
				// @todo Add web viewing for groups other than WikiPageMessageGroup
				$pageTranslation = $this->getConfig()->get( 'EnablePageTranslation' );
				if ( $pageTranslation && $group instanceof WikiPageMessageGroup ) {
					$collection->loadTranslations();
					$page = TranslatablePage::newFromTitle( $group->getTitle() );
					$text = $page->getParse()->getTranslationPageText( $collection );
					$displayTitle = $page->getPageDisplayTitle( $this->language );
					if ( $displayTitle ) {
						$text = "{{DISPLAYTITLE:$displayTitle}}$text";
					}
					$box = Html::element(
						'textarea',
						array( 'id' => 'wpTextbox', 'rows' => 50, ),
						$text
					);
					$out->addHTML( $box );
					return;
				}

				// This should have been prevented at validation. See checkInput().
				throw new Exception( 'Unexpected export format.' );
		}
	}

	private function setupCollection( MessageGroup $group ) {
		$collection = $group->initCollection( $this->language );

		// Don't export ignored, unless it is the source language or message documentation
		$translateDocCode = $this->getConfig()->get( 'TranslateDocumentationLanguageCode' );
		if ( $this->language !== $translateDocCode
			// && $this->language !== $group->getSourceLanguage() is this needed??
		) {
			$collection->filter( 'ignored' );
		}

		return $collection;
	}

	protected function getGroupName() {
		return 'wiki';
	}
}
