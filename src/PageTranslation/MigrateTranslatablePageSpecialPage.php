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

		$errorDiv = Html::rawElement(
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

		$summaryHidden = Html::hidden(
			'',
			$this->msg( 'pm-summary-import' )->inContentLanguage()->text(),
			[ 'id' => 'pm-summary' ]
		);

		$textInput = Html::rawElement(
			'div',
			[ 'class' => 'cdx-text-input' ],
			Html::input(
				'',
				'',
				'text',
				[
					'id' => 'title',
					'class' => 'mw-searchInput cdx-text-input__input',
					'data-mw-searchsuggest' => FormatJson::encode( [ 'wrapAsLink' => false ] ),
					'placeholder' => $this->msg( 'pm-pagetitle-placeholder' )->text(),
				]
			)
		);

		$importButton = Html::element(
			'button',
			[
				'id' => 'action-import',
				'class' => 'cdx-button cdx-button--action-progressive cdx-button--weight-primary',
				'type' => 'button',
			],
			$this->msg( 'pm-import-button-label' )->text()
		);

		$saveButton = Html::element(
			'button',
			[
				'id' => 'action-save',
				'class' => 'cdx-button cdx-button--action-progressive cdx-button--weight-primary hide',
				'type' => 'button',
			],
			$this->msg( 'pm-savepages-button-label' )->text()
		);

		$cancelButton = Html::element(
			'button',
			[
				'id' => 'action-cancel',
				'class' => 'cdx-button cdx-button--action-default cdx-button--weight-quiet hide',
				'type' => 'button',
			],
			$this->msg( 'pm-cancel-button-label' )->text()
		);

		$form = Html::rawElement(
			'form',
			[
				'class' => 'mw-tpm-sp-form row',
				'id' => 'mw-tpm-sp-primary-form',
				'action' => '',
			],
			$summaryHidden . "\n" . $textInput . "\n" . $importButton . "\n" . $saveButton . "\n" . $cancelButton
		);

		$instructions = Html::element( 'div', [ 'class' => 'mw-tpm-sp-instructions hide' ] );
		$unitListing = Html::element( 'div', [ 'class' => 'mw-tpm-sp-unit-listing' ] );

		$container = Html::rawElement(
			'div',
			[ 'class' => 'mw-tpm-sp-container grid' ],
			$errorDiv . $form . $instructions . $unitListing
		);

		$output->addHTML( $container );
		$output->addHTML(
			Html::errorBox(
				$this->msg( 'tux-nojs' )->escaped(),
				'',
				'tux-nojs'
			)
		);
	}
}
