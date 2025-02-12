<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Html\Html;
use MediaWiki\Json\FormatJson;
use MediaWiki\SpecialPage\SpecialPage;

/**
 * Contains code for Special:PageMigration to migrate to page transation
 * @author Pratik Lahoti
 * @license GPL-2.0-or-later
 */
class MigrateTranslatablePageSpecialPage extends SpecialPage {
	public function __construct() {
		parent::__construct( 'PageMigration', 'pagetranslation' );
	}

	protected function getGroupName(): string {
		return 'translation';
	}

	/** @inheritDoc */
	public function getDescription() {
		return $this->msg( 'pagemigration' );
	}

	/** @inheritDoc */
	public function execute( $par ) {
		$output = $this->getOutput();
		$this->setHeaders();
		$this->checkPermissions();
		$this->addHelpLink( 'Help:Extension:Translate/Page translation administration' );
		$this->outputHeader( 'pagemigration-summary' );
		$output->addModules( 'ext.translate.special.pagemigration' );
		$output->addModuleStyles( [
			'ext.translate.specialpages.styles',
			'jquery.uls.grid',
			'mediawiki.codex.messagebox.styles',
		] );

		# Do stuff
		# ...
		$out = '';
		$out .= Html::openElement( 'div', [ 'class' => 'mw-tpm-sp-container grid' ] );
		$out .= Html::openElement( 'div', [ 'class' => 'mw-tpm-sp-error row',
			'id' => 'mw-tpm-sp-error-div' ] );
		$out .= Html::element( 'div',
			[ 'class' => 'mw-tpm-sp-error__message five columns hide' ] );
		$out .= Html::closeElement( 'div' );
		$out .= Html::openElement( 'form', [ 'class' => 'mw-tpm-sp-form row',
			'id' => 'mw-tpm-sp-primary-form', 'action' => '' ] );
		$out .= Html::element( 'input', [ 'id' => 'pm-summary', 'type' => 'hidden',
			'value' => $this->msg( 'pm-summary-import' )->inContentLanguage()->text() ] );
		$out .= "\n";
		$out .= Html::element( 'input', [ 'id' => 'title', 'class' => 'mw-searchInput mw-ui-input',
			'data-mw-searchsuggest' => FormatJson::encode( [
				'wrapAsLink' => false
			] ), 'placeholder' => $this->msg( 'pm-pagetitle-placeholder' )->text() ] );
		$out .= "\n";
		$out .= Html::element( 'input', [ 'id' => 'action-import',
			'class' => 'mw-ui-button mw-ui-progressive', 'type' => 'button',
			'value' => $this->msg( 'pm-import-button-label' )->text() ] );
		$out .= "\n";
		$out .= Html::element( 'input', [ 'id' => 'action-save',
			'class' => 'mw-ui-button mw-ui-progressive hide', 'type' => 'button',
			'value' => $this->msg( 'pm-savepages-button-label' )->text() ] );
		$out .= "\n";
		$out .= Html::element( 'input', [ 'id' => 'action-cancel',
			'class' => 'mw-ui-button mw-ui-quiet hide', 'type' => 'button',
			'value' => $this->msg( 'pm-cancel-button-label' )->text() ] );
		$out .= Html::closeElement( 'form' );
		$out .= Html::element( 'div', [ 'class' => 'mw-tpm-sp-instructions hide' ] );
		$out .= Html::rawElement( 'div', [ 'class' => 'mw-tpm-sp-unit-listing' ] );
		$out .= Html::closeElement( 'div' );

		$output->addHTML( $out );
		$output->addHTML(
			Html::errorBox(
				$this->msg( 'tux-nojs' )->escaped(),
				'',
				'tux-nojs'
			)
		);
	}
}
