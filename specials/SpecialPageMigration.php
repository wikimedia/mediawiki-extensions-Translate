<?php
/**
 * Contains code for special page Special:PageMigration
 *
 * @file
 * @author Pratik Lahoti
 * @copyright Copyright © 2014-2015 Pratik Lahoti
 * @license GPL-2.0+
 */

class SpecialPageMigration extends SpecialPage {
	function __construct() {
		parent::__construct( 'PageMigration', 'pagetranslation' );
	}

	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
		$this->checkPermissions();
		$output->addModules( 'ext.translate.special.pagemigration' );
		# Get request data from, e.g.
		$param = $request->getText( 'param' );
		# Do stuff
		# ...
		$out = '';
		$out .= Html::openElement( 'div', array( 'id' => 'mw-tpm-sp-pageinput' ) );
		$out .= Html::element( 'input', array( 'id' => 'mw-tpm-sp-pageinput_pagename', 'type' => 'text',
			'placeholder' => $this->msg( 'pm-pagename-placeholder' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'mw-tpm-sp-pageinput_langcode', 'type' => 'text',
			'placeholder' => $this->msg( 'pm-langcode-placeholder' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'mw-tpm-sp-pageinput_button-import',
			'type' => 'button', 'value' => $this->msg( 'pm-import-button-label' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'mw-tpm-sp-pageinput_button-save',
			'type' => 'button', 'value' => $this->msg( 'pm-savepages-button-label' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'mw-tpm-sp-pageinput_button-cancel',
			'type' => 'button', 'value' => $this->msg( 'pm-cancel-button-label' )->text() ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::element( 'br' );
		$out .= Html::openElement( 'div', array( 'id' => 'mw-tpm-sp-sourceunits' ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::openElement( 'div', array( 'id' => 'mw-tpm-sp-translationunits' ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::openElement( 'div', array( 'id' => 'mw-tpm-sp-action-items' ) );
		$out .= Html::closeElement( 'div' );
		$output->addHTML( $out );
	}
}
