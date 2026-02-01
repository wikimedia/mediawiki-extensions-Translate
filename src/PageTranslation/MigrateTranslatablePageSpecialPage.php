<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;

/**
 * Contains code for Special:PageMigration to migrate to page transation
 * @author Pratik Lahoti
 * @license GPL-2.0-or-later
 */
class MigrateTranslatablePageSpecialPage extends SpecialPage {
	public function __construct() {
		parent::__construct( 'PageMigration' );
	}

	/** @inheritDoc */
	public function getRestriction(): string {
		return 'pagetranslation';
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
			'mediawiki.codex.messagebox.styles',
		] );

		$output->addHTML( Html::element(
			'div',
			[
				'id' => 'mw-tpm-sp-container',
				'data-editsummary' => $this->msg( 'pm-summary-import' )->inContentLanguage()->text(),
			]
		) );
		$output->addHTML(
			Html::errorBox(
				$this->msg( 'tux-nojs' )->escaped(),
				'',
				'tux-nojs'
			)
		);
	}
}
