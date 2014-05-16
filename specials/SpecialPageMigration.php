<?php
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
		$output->addHTML( "<div id='pageinput'>
			<input type='text' id='pagename' placeholder='Enter page name'/>
			<input type='text' id='langcode' placeholder='langcode'/>
			<input type='submit' id='buttonImport' value='Import'/>
			</div>");
		$output->addHTML("<br/><div id='sourceunits'></div>
			<div id='translationunits'></div>");
	}
}
