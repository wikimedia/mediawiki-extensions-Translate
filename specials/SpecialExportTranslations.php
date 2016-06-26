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
		// Check whether these are actually needed!!
		$out->addModules( 'ext.translate.special.translate.legacy' );
		$out->addModuleStyles( 'ext.translate.legacy' );

		$this->group = $request->getText( 'group', $par );
		$this->language = $request->getVal( 'language', $lang->getCode() );
		$this->format = $request->getVal( 'task' ); // 'task' is kept for legacy reasons
		$this->format = $request->getText( 'format', $this->format );

		$this->outputForm();

		if ( $this->group ) {
			$status = $this->checkInput();
			if ( !$status->isGood() ) {
				$errors = $out->parse( $status->getWikiText( false, false, $lang ) );
				$out->addHTML( Html::rawElement( 'div', array( 'class' => 'error' ), $errors ) );
				return;
			}

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
		foreach ( $this->getValidFormats() as $format ) {
			$options[ $this->msg( "translate-taskui-$format" )->escaped() ] = $format;
		}
		return $options;
	}

	/**
	 * @return string[]
	 */
	public function getValidFormats() {
		// @todo FIXME: Implement web textarea; otherwise not possible
		// to retrieve for translation pages
		return array( 'export-as-po', 'export-to-file' );
	}

	/**
	 * @return Status
	 */
	protected function checkInput() {
		$status = Status::newGood();

		$msgGroup = MessageGroups::getGroup( $this->group );
		if ( $msgGroup === null ) {
			$status->fatal( 'translate-page-no-such-group' );
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

		if ( !in_array( $this->format, $this->getValidFormats() ) ) {
			$status->fatal( 'translate-export-invalid-format' );
		}

		return $status;
	}

	protected function getGroupName() {
		return 'wiki';
	}
}
