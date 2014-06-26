<?php
/**
 * Contains code for special page Special:PagePreparation
 *
 * @file
 * @author Pratik Lahoti
 * @copyright Copyright © 2014 Pratik Lahoti
 * @license GPL-2.0+
 */

class SpecialPagePreparation extends SpecialPage {
	function __construct() {
		parent::__construct( 'PagePreparation', 'pagetranslation' );
	}

	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
		$this->checkPermissions();
		$pagenamePlaceholder = $this->msg( 'pp-pagename-placeholder' )->escaped();
		$prepareButtonValue = $this->msg( 'pp-prepare-button-label' )->escaped();
		$saveButtonValue = $this->msg( 'pp-save-button-label' )->escaped();
		$output->addModules( 'ext.translate.pagepreparation' );
		$output->addModuleStyles( 'jquery.uls.grid' );
		$param = $request->getText( 'param' );

		$out = '';
		$diff = new DifferenceEngine( $this->getContext() );
		$diffHeader = $diff->addHeader( " ", $this->msg( 'pp-diff-old-header' )->escaped(),
			$this->msg( 'pp-diff-new-header' )->escaped() );

		$out = <<<HTML
<div class="grid">
	<form class="mw-tpp-sp-form row" name="mw-tpp-sp-input-form">
		<input name="title" id="title" class="mw-searchInput"
			placeholder="{$pagenamePlaceholder}" />
		<button id="action-prepare" class="mw-ui-button mw-ui-primary" type="button">
			{$prepareButtonValue}</button>
		<button id="action-save" class="mw-ui-button mw-ui-constructive hide" type="button">
			{$saveButtonValue}</button>
	</form>
	<div class="messageDiv hide"></div>
	<div class="divDiff hide">
		{$diffHeader}
	</div>
</div>
HTML;
		$output->addHTML( $out );
	}
}
