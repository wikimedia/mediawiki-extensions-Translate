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
		$output->addModuleStyles( 'jquery.uls.grid' );
		# Get request data from, e.g.
		$param = $request->getText( 'param' );
		# Do stuff
		# ...
		$out = '';
		$out .= Html::openElement( 'div', array( 'class' => 'grid' ) );
		$out .= Html::openElement( 'form', array( 'class' => 'mw-tpm-sp-form row',
			'id' => 'mw-tpm-sp-primary-form' ) );
		$out .= Html::element( 'input', array( 'id' => 'title', 'class' => 'mw-searchInput',
			'placeholder' => $this->msg( 'pm-pagename-placeholder' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'language', 'type' => 'text',
			'placeholder' => $this->msg( 'pm-langcode-placeholder' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'action-import',
			'class' => 'mw-ui-button mw-ui-primary','type' => 'button',
			'value' => $this->msg( 'pm-import-button-label' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'action-save',
			'class' => 'mw-ui-button mw-ui-constructive','type' => 'button',
			'value' => $this->msg( 'pm-savepages-button-label' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'action-cancel',
			'class' => 'mw-ui-button mw-ui-quiet','type' => 'button',
			'value' => $this->msg( 'pm-cancel-button-label' )->text() ) );
		$out .= Html::closeElement( 'form' );
		$out .= Html::openElement( 'div', array( 'class' => 'mw-tpm-sp-unit-listing' ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::closeElement( 'div' );

		$output->addHTML( $out );
	}
}
