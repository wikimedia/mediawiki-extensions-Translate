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
	public function __construct() {
		parent::__construct( 'PageMigration', 'pagetranslation' );
	}

	protected function getGroupName() {
		return 'wiki';
	}

	function getDescription() {
		return $this->msg( 'pagemigration' )->text();
	}

	public function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
		$this->checkPermissions();
		$this->outputHeader( 'pagemigration-summary' );
		$output->addModules( 'ext.translate.special.pagemigration' );
		$output->addModuleStyles( 'jquery.uls.grid' );
		# Get request data from, e.g.
		$param = $request->getText( 'param' );
		# Do stuff
		# ...
		$out = '';
		$out .= Html::openElement( 'div', array( 'class' => 'grid' ) );
		$out .= Html::openElement( 'div', array( 'class' => 'mw-tpm-sp-error row',
			'id' => 'mw-tpm-sp-error-div' ) );
		$out .= Html::element( 'div',
			array( 'class' => 'mw-tpm-sp-error__message five columns hide' ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::openElement( 'form', array( 'class' => 'mw-tpm-sp-form row',
			'id' => 'mw-tpm-sp-primary-form' ) );
		$out .= Html::element( 'input', array( 'id' => 'pm-summary', 'type' => 'hidden',
			'value' => $this->msg( 'pm-summary-import' )->inContentLanguage()->text() ) );
		$out .= "\n";
		$out .= Html::element( 'input', array( 'id' => 'title', 'class' => 'mw-searchInput mw-ui-input',
			'placeholder' => $this->msg( 'pm-pagetitle-placeholder' )->text() ) );
		$out .= "\n";
		$out .= Html::element( 'input', array( 'id' => 'action-import',
			'class' => 'mw-ui-button mw-ui-primary', 'type' => 'button',
			'value' => $this->msg( 'pm-import-button-label' )->text() ) );
		$out .= "\n";
		$out .= Html::element( 'input', array( 'id' => 'action-save',
			'class' => 'mw-ui-button mw-ui-constructive hide', 'type' => 'button',
			'value' => $this->msg( 'pm-savepages-button-label' )->text() ) );
		$out .= "\n";
		$out .= Html::element( 'input', array( 'id' => 'action-cancel',
			'class' => 'mw-ui-button mw-ui-quiet hide', 'type' => 'button',
			'value' => $this->msg( 'pm-cancel-button-label' )->text() ) );
		$out .= Html::closeElement( 'form' );
		$out .= Html::element( 'div', array( 'class' => 'mw-tpm-sp-instructions hide' ) );
		$out .= Html::openElement( 'div', array( 'class' => 'mw-tpm-sp-unit-listing' ) );
		$out .= Html::closeElement( 'div' );
		$out .= Html::closeElement( 'div' );

		$output->addHTML( $out );
	}
}
