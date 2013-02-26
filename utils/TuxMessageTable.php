<?php

class TuxMessageTable extends ContextSource {
	protected $group;
	protected $language;

	public function __construct( IContextSource $context, MessageGroup $group, $language ) {
		$this->setContext( $context );
		$this->group = $group;
		$this->language = $language;
	}

	public function header() {
		$sourceLang = Language::factory( $this->group->getSourceLanguage() );
		$targetLang = Language::factory( $this->language );

		return Xml::openElement( 'div', array(
			'class' => 'row tux-messagelist',
			'data-sourcelangcode' => $sourceLang->getCode(),
			'data-sourcelangdir' => $sourceLang->getDir(),
			'data-targetlangcode' => $targetLang->getCode(),
			'data-targetlangdir' => $targetLang->getDir(),
		) );
	}

	public function fullTable() {
		$modules = array( 'ext.translate.editor' );
		wfRunHooks( 'TranslateBeforeAddModules', array( &$modules ) );
		$this->getOutput()->addModules( $modules );

		$batchSize = 100;

		$footer = Html::openElement( 'div',
			array(
				'class' => 'tux-messagetable-loader hide',
				'data-messagegroup' => $this->group->getId(),
				'data-pagesize' => $batchSize,
			) )
			. '<span class="tux-loading-indicator"></span>'
			. '<div class="tux-messagetable-loader-count"></div>'
			. '<div class="tux-messagetable-loader-more">'
			. $this->msg( 'tux-messagetable-loading-messages' )->numParams( $batchSize )->escaped()
			. '</div>'
			. Html::closeElement( 'div' );

		$footer .= '<div class="tux-action-bar row">'
			. Html::element( 'div',
				array(
					'class' => 'three columns tux-message-list-statsbar',
					'data-messagegroup' => $this->group->getId(),
				) );

		//$footer .= '<div class="six columns text-center">';

		// Hide this button by default and show it only if the view is relevant
		$footer .= '<button class="three column toggle-button tux-proofread-own-translations-button hide-own">'
			// Which label should be shown by default?
			. $this->msg( 'tux-editor-proofreading-hide-own-translations' )->escaped()
			. '</button>';

		// Hide this button by default and show it only if the filter is relevant
		$footer .= '<button class="three column toggle-button tux-editor-clear-translated hide">'
			. $this->msg( 'tux-editor-clear-translated' )->escaped()
			. '</button>';

		//$footer .= '</div>';

		$footer .= '<div class="six columns text-center">'
			. '<button class="three column toggle-button down translate-mode-button">'
			. $this->msg( 'tux-editor-translate-mode' )->escaped()
			. '</button>';

		if ( $this->getUser()->isallowed( 'translate-messagereview' ) ) {
			$footer .=  '<button class="three column toggle-button tux-proofread-button">'
				. $this->msg( 'tux-editor-proofreading-mode' )->escaped()
				. '</button>';
		}

		$footer .= '</div>';

		// Actual message table is fetched and rendered at client side. This just provides
		// the loader and action bar.
		return $this->header() . $footer . '</div>';
	}
}
