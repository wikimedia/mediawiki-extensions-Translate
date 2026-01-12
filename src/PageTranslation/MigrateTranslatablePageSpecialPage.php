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

		$out = '';
		$out .= Html::openElement( 'div', [ 'class' => 'mw-tpm-sp-container grid' ] );
		$out .= Html::rawElement(
			'div',
			[
				'class' => 'mw-tpm-sp-error row',
				'id' => 'mw-tpm-sp-error-div',
			],
			Html::element(
				'div',
				[ 'class' => 'mw-tpm-sp-error__message five columns hide' ]
			)
		);
		$out .= Html::openElement(
			'form',
			[
				'class' => 'mw-tpm-sp-form row',
				'id' => 'mw-tpm-sp-primary-form',
				'action' => '',
			]
		);
		$out .= Html::hidden(
			'',
			$this->msg( 'pm-summary-import' )->inContentLanguage()->text(),
			[ 'id' => 'pm-summary' ]
		) . "\n";
		$out .= Html::input(
			'',
			'',
			'text',
			[
				'id' => 'title',
				'class' => 'mw-searchInput mw-ui-input',
				'data-mw-searchsuggest' => FormatJson::encode( [ 'wrapAsLink' => false ] ),
				'placeholder' => $this->msg( 'pm-pagetitle-placeholder' )->text(),
			]
		) . "\n";
		$out .= Html::input(
			'',
			$this->msg( 'pm-import-button-label' )->text(),
			'button',
			[
				'id' => 'action-import',
				'class' => 'mw-ui-button mw-ui-progressive',
			]
		) . "\n";
		$out .= Html::input(
			'',
			$this->msg( 'pm-savepages-button-label' )->text(),
			'button',
			[
				'id' => 'action-save',
				'class' => 'mw-ui-button mw-ui-progressive hide',
			]
		) . "\n";
		$out .= Html::input(
			'',
			$this->msg( 'pm-cancel-button-label' )->text(),
			'button',
			[
				'id' => 'action-cancel',
				'class' => 'mw-ui-button mw-ui-quiet hide',
			]
		);
		$out .= Html::closeElement( 'form' );
		$out .= Html::element( 'div', [ 'class' => 'mw-tpm-sp-instructions hide' ] );
		$out .= Html::element( 'div', [ 'class' => 'mw-tpm-sp-unit-listing' ] );
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
