<?php
/**
 * TranslationStash - Translator screening page
 *
 * @file
 * @author Santhosh Thottingal
 * @license GPL2+
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

}
