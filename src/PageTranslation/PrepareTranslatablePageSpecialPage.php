<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use DifferenceEngine;
use MediaWiki\Html\Html;
use MediaWiki\Html\TemplateParser;
use MediaWiki\SpecialPage\SpecialPage;

/**
 * Contains code to prepare a page for translation
 * @author Pratik Lahoti
 * @license GPL-2.0-or-later
 */
class PrepareTranslatablePageSpecialPage extends SpecialPage {
	private TemplateParser $templateParser;

	public function __construct() {
		parent::__construct( 'PagePreparation', 'pagetranslation' );
		$this->templateParser = new TemplateParser( __DIR__ . '/templates' );
	}

	protected function getGroupName(): string {
		return 'translation';
	}

	/** @inheritDoc */
	public function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();
		$this->checkPermissions();
		$this->outputHeader();

		$output->addModules( 'ext.translate.special.pagepreparation' );
		$output->addModuleStyles( [
			'ext.translate.specialpages.styles',
			'codex-styles'
		] );

		$output->addHTML(
			$this->getHtml( $request->getText( 'page', $par ?? '' ) )
		);
		$output->addHTML(
			Html::errorBox(
				$this->msg( 'tux-nojs' )->escaped(),
				'',
				'tux-nojs'
			)
		);
	}

	public function getHtml( string $inputValue ): string {
		$diff = new DifferenceEngine( $this->getContext() );
		$diffHeader = $diff->addHeader( ' ', $this->msg( 'pp-diff-old-header' )->escaped(),
			$this->msg( 'pp-diff-new-header' )->escaped() );

		$data = [
			'pagenamePlaceholder' => $this->msg( 'pp-pagename-placeholder' )->text(),
			'prepareButtonLabel' => $this->msg( 'pp-prepare-button-label' )->text(),
			'saveButtonLabel' => $this->msg( 'pp-save-button-label' )->text(),
			'cancelButtonLabel' => $this->msg( 'pp-cancel-button-label' )->text(),
			'summaryValue' => $this->msg( 'pp-save-summary' )->inContentLanguage()->text(),
			'inputValue' => $inputValue,
			'diffHeaderHtml' => $diffHeader
		];

		return $this->templateParser->processTemplate( 'PrepareTranslatablePageTemplate', $data );
	}
}
