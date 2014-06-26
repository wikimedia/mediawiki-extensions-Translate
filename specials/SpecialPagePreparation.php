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
			'id' => 'mw-tpp-sp-primary-form' ) );
		$out .= Html::element( 'input', array( 'id' => 'title', 'class' => 'mw-searchInput',
			'placeholder' => $this->msg( 'pp-pagename-placeholder' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'action-prepare',
			'class' => 'mw-ui-button mw-ui-primary', 'type' => 'button',
			'value' => $this->msg( 'pp-prepare-button-label' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'action-save',
			'class' => 'mw-ui-button mw-ui-constructive hide', 'type' => 'button',
			'value' => $this->msg( 'pp-save-button-label' )->text() ) );
		$out .= Html::closeElement( 'form' );
		$out .= Html::closeElement( 'div' );

		$output->addHTML( $out );
	}
}
