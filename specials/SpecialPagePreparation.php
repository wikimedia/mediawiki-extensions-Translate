<?php
/**
 * Contains code for special page Special:PagePreparation
 *
 * @file
 * @author Pratik Lahoti
 * @copyright Copyright © 2014-2015 Pratik Lahoti
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
		$output->addModules( 'ext.translate.pagepreparation' );
		$output->addModuleStyles( 'jquery.uls.grid' );
		# Get request data from, e.g.
		$param = $request->getText( 'param' );
		# Do stuff
		# ...
		$out = '';
		$out .= Html::openElement( 'div', array( 'class' => 'grid' ) );
		$out .= Html::openElement( 'form', array( 'class' => 'mw-tpp-sp-form row',
			'id' => 'mw-tpp-sp-primary-form', 'name' => 'mw-tpp-sp-input-form' ) );
		$out .= Html::element( 'input', array( 'name' => 'title', 'id' => 'title',
			'class' => 'mw-searchInput',
			'placeholder' => $this->msg( 'pp-pagename-placeholder' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'action-prepare',
			'class' => 'mw-ui-button mw-ui-primary', 'type' => 'button',
			'value' => $this->msg( 'pp-prepare-button-label' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'action-save',
			'class' => 'mw-ui-button mw-ui-constructive hide', 'type' => 'button',
			'value' => $this->msg( 'pp-save-button-label' )->text() ) );
		$out .= Html::closeElement( 'form' );
		$out .= Html::element( 'div', array( 'class' => 'messageDiv hide' ) );
		$out .= Html::openElement( 'div', array( 'class' => 'divDiff hide' ) );
		$out .= Html::openElement( 'table', array( 'class' => 'diff diff-contentalign-left' ) );
		$out .= Html::openElement( 'colgroup' );
		$out .= Html::element( 'col', array( 'class' => 'diff-marker' ) );
		$out .= Html::element( 'col', array( 'class' => 'diff-content' ) );
		$out .= Html::element( 'col', array( 'class' => 'diff-marker' ) );
		$out .= Html::element( 'col', array( 'class' => 'diff-content' ) );
		$out .= Html::closeElement( 'colgroup' );
		$out .= Html::element( 'tbody', array( 'id' => 'diff-body' ) );
		$out .= Html::closeElement( 'table' );
		$out .= Html::closeElement( 'div' );
		$out .= Html::closeElement( 'div' );
		$output->addHTML( $out );
	}
}
