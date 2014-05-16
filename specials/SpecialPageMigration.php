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
		$out .= Html::openElement( 'input', array( 'id' => 'pagename', 'type' => 'text', 'placeholder' => 'Enter page name' ) );
		$out .= Html::openElement( 'input', array( 'id' => 'langcode', 'type' => 'text', 'placeholder' => 'langcode' ) );
		$out .= Html::openElement( 'input', array( 'id' => 'buttonImport', 'type' => 'submit', 'value' => 'Import' ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::openElement( 'br' );
		$out .= Html::openElement( 'div', array( 'id' => 'sourceunits' ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::openElement( 'div', array( 'id' => 'translationunits' ) );
		$out .= Html::closeElement( 'div' );
		
		$output->addHTML($out);
		// $output->addHTML( "<div id='pageinput'>
		// 	<input type='text' id='pagename' placeholder='Enter page name'/>
		// 	<input type='text' id='langcode' placeholder='langcode'/>
		// 	<input type='submit' id='buttonImport' value='Import'/>
		// 	</div>");
		// $output->addHTML("<br/><div id='sourceunits'></div>
		// 	<div id='translationunits'></div>");
	}
}
