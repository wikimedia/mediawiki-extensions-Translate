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
class SpecialTranslationStash extends SpecialPage {
	///< @param TranslationStashStorage
	protected $stash;

	function __construct() {
		parent::__construct( 'TranslationStash' );
	}

	public function execute( $params ) {
		$this->setHeaders();
		$out = $this->getOutput();

		$this->stash = new TranslationStashStorage( wfGetDB( DB_MASTER ) );

		if ( !$this->hasPermissionToUse() ) {
			$out->redirect( Title::newMainPage()->getLocalUrl() );

			return;
		}

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
		// Easier to do this way than in JS
		// @todo, but move to JS once it is easier there
		$token = Html::hidden( 'token', ApiTranslationStash::getToken(),
			array( 'id' => 'translationstash-token' ) );
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
	$token
</div>
HTML
		);
	}

	protected function getMessageTable() {
		$sourceLang = Language::factory('en');
		$targetLang = $this->getLanguage();

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
		$language = $this->getLanguage();
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

}
