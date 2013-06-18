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
	function __construct() {
		parent::__construct( 'TranslationStash' );
	}

	public function execute( $params ) {
		$this->setHeaders();
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.translationstash' );
		$this->showPage();
	}

	/**
	 * Generates the whole page html and appends it to output
	 */
	protected function showPage() {
		// Easier to do this way than in JS
		$out = $this->getOutput();
		$out->addHtml( <<<HTML
<div class="grid">
	<div class="row translate-welcome-header">
		<h1>{$this->msg( 'translate-translationstash-welcome',
			$this->getUser()->getName() )->parse()}
		</h1>
		<p>{$this->msg( 'translate-translationstash-welcome-note' )->parse()}</p>
	</div>
	<div class="row translate-stash-control">
		<div class="six columns stash-stats">
		{$this->msg( 'translate-translationstash-initialtranslation' )->parse()}</div>
		{$this->tuxLanguageSelector()}
	</div>
	{$this->getMessageTable()}
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
		$targetLangCode = $this->getLanguage()->getCode();
		$targetLangDir = $this->getLanguage()->getDir();
		$targetLangName = Language::fetchLanguageName( $targetLangCode );

		// No-break space is added for spacing after the label
		// and to ensure separation of words (in Arabic, for example)
		return Html::rawElement( 'div',
			array( 'class' => 'six columns ext-translate-language-selector right' ),
			Html::element( 'span',
				array( 'class' => 'ext-translate-language-selector-label' ),
				$this->msg( 'tux-languageselector' )->text()
			) .
				'&#160;' . // nbsp
				Html::element( 'span',
					array(
						'class' => 'uls',
						'lang' => $targetLangCode,
						'dir' => $targetLangDir,
					),
					$targetLangName
				)
		);
	}

}
