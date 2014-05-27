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
		$output->addModules( 'ext.translate.special.pagemigration' );
		# Get request data from, e.g.
		$param = $request->getText( 'param' );
		# Do stuff
		# ...
		$out = '';
		$out .= Html::openElement( 'div', array( 'id' => 'pageinput' ) );
		$out .= Html::element( 'input', array( 'id' => 'pagename', 'type' => 'text',
			'placeholder' => $this->msg( 'pm-pagename-placeholder' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'langcode', 'type' => 'text',
			'placeholder' => $this->msg( 'pm-langcode-placeholder' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'buttonImport', 'type' => 'button',
			'value' => $this->msg( 'pm-import-button-label' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'buttonSavePages', 'type' => 'button',
			'value' => $this->msg( 'pm-savepages-button-label' )->text() ) );
		$out .= Html::element( 'input', array( 'id' => 'buttonCancel', 'type' => 'button',
			'value' => $this->msg( 'pm-cancel-button-label' )->text() ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::element( 'br' );
		$out .= Html::openElement( 'div', array( 'id' => 'sourceunits' ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::openElement( 'div', array( 'id' => 'translationunits' ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::openElement( 'div', array( 'id' => 'actions' ) );
		$out .= Html::closeElement( 'div' );
		$output->addHTML( $out );
	}
}
