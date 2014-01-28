<?php
/**
 * TranslationStash - Translator screening page
 *
 * @file
 * @author Santhosh Thottingal
 * @license GPL-2.0+
 */

/**
 * Special page for new users to translate example messages.
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialTranslationStash extends TranslateSpecialPage {
	/** @var TranslationStashStorage */
	protected $stash;

	function __construct() {
		parent::__construct( 'TranslationStash' );
	}

	public function execute( $params ) {
		global $wgTranslateSandboxLimit;

		$this->setHeaders();
		$out = $this->getOutput();

		$this->stash = new TranslationStashStorage( wfGetDB( DB_MASTER ) );

		if ( !$this->hasPermissionToUse() ) {

			if ( $this->getUser()->isLoggedIn() ) {
				$out->redirect( '/wiki/Project:Translator' );
				return;
			}

			$out->redirect( Title::newMainPage()->getLocalUrl() );

			return;
		}

		$out->addJsConfigVars( 'wgTranslateSandboxLimit', $wgTranslateSandboxLimit );
		$out->addModules( 'ext.translate.special.translationstash' );
		$this->showPage();
	}

	/**
	 * Checks that the user is in the sandbox. Also handles special overrides
	 * mainly used for integration testing.
	 *
	 * @return bool
	 */
	protected function hasPermissionToUse() {
		global $wgTranslateTestUsers;

		$request = $this->getRequest();
		$user = $this->getUser();

		if ( in_array( $user->getName(), $wgTranslateTestUsers, true ) ) {
			if ( $request->getVal( 'integrationtesting' ) === 'activatestash' ) {
				$user->addGroup( 'translate-sandboxed' );

				return true;
			} elseif ( $request->getVal( 'integrationtesting' ) === 'deactivatestash' ) {
				$user->removeGroup( 'translate-sandboxed' );
				$this->stash->deleteTranslations( $user );

				return false;
			}
		}

		if ( !TranslateSandbox::isSandboxed( $user ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Generates the whole page html and appends it to output
	 */
	protected function showPage() {
		$out = $this->getOutput();
		$user = $this->getUser();

		$count = count( $this->stash->getTranslations( $user ) );
		if ( $count === 0 ) {
			$progress = $this->msg( 'translate-translationstash-initialtranslation' )->parse();
		} else {
			$progress = $this->msg( 'translate-translationstash-translations' )
				->numParams( $count )->parse();
		}

		$out->addHtml( <<<HTML
<div class="grid">
	<div class="row translate-welcome-header">
		<h1>
			{$this->msg( 'translate-translationstash-welcome', $user->getName() )->parse()}
		</h1>
		<p>
			{$this->msg( 'translate-translationstash-welcome-note' )->parse()}
		</p>
	</div>
	<div class="row translate-stash-control">
		<div class="six columns stash-stats">
			{$progress}
		</div>
		<div class="six columns ext-translate-language-selector right">
			{$this->tuxLanguageSelector()}
		</div>
	</div>
	{$this->getMessageTable()}
	<div class="row limit-reached hide"></div>
</div>
HTML
		);
	}

	protected function getMessageTable() {
		$sourceLang = $this->getSourceLanguage();
		$targetLang = $this->getTargetLanguage();

		$list = Html::element( 'div', array(
			'class' => 'row tux-messagelist',
			'data-sourcelangcode' => $sourceLang->getCode(),
			'data-sourcelangdir' => $sourceLang->getDir(),
			'data-targetlangcode' => $targetLang->getCode(),
			'data-targetlangdir' => $targetLang->getDir(),
		) );

		return $list;
	}

	protected function tuxLanguageSelector() {
		// The name will be displayed in the UI language,
		// so use for lang and dir
		$language = $this->getTargetLanguage();
		$targetLangName = Language::fetchLanguageName( $language->getCode() );

		$label = Html::element(
			'span',
			array( 'class' => 'ext-translate-language-selector-label' ),
			$this->msg( 'tux-languageselector' )->text()
		);

		$trigger = Html::element(
			'span',
			array(
				'class' => 'uls',
				'lang' => $language->getCode(),
				'dir' => $language->getDir(),
			),
			$targetLangName
		);

		// No-break space is added for spacing after the label
		// and to ensure separation of words (in Arabic, for example)
		return "$label&#160;$trigger";
	}

	/**
	 * Returns the source language for messages.
	 * @return Language
	 */
	protected function getSourceLanguage() {
		// Bad
		return Language::factory( 'en' );
	}

	/**
	 * Returns the default target language for messages.
	 * @return Language
	 */
	protected function getTargetLanguage() {
		$ui = $this->getLanguage();
		$source = $this->getSourceLanguage();
		if ( $ui->getCode() !== $source->getCode() ) {
			return $ui;
		}

		$options = FormatJson::decode( $this->getUser()->getOption( 'translate-sandbox' ), true );
		$supported = TranslateUtils::getLanguageNames( 'en' );

		if ( isset( $options['languages' ] ) ) {
			foreach ( $options['languages'] as $code ) {
				if ( !isset( $supported[$code] ) ) {
					continue;
				}

				if ( $code !== $source->getCode() ) {
					return Language::factory( $code );
				}
			}
		}

		// User has not chosen any valid language. Pick one at random.
		$codes = array_keys( $supported );
		return Language::factory( $codes[rand( 0, count( $codes ) - 1 )] );
	}
}
