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
		parent::__construct( 'PageMigration' );
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
					'placeholder' => 'Enter page name' ) );
		$out .= Html::element( 'input', array( 'id' => 'langcode', 'type' => 'text',
					'placeholder' => 'langcode' ) );
		$out .= Html::element( 'input', array( 'id' => 'buttonImport', 'type' => 'button',
					'value' => 'Import' ) );
		$out .= Html::element( 'input', array( 'id' => 'buttonSavePages', 'type' => 'button',
					'value' => 'Save' ) );
		$out .= Html::element( 'input', array( 'id' => 'buttonCancel', 'type' => 'button',
					'value' => 'Cancel' ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::openElement( 'br' );
		$out .= Html::openElement( 'div', array( 'id' => 'sourceunits' ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::openElement( 'div', array( 'id' => 'translationunits' ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::openElement( 'div', array( 'id' => 'actions' ) );
		$out .= Html::closeElement( 'div' );
		$output->addHTML( $out );
	}
}
