<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use DateTime;
use DateTimeZone;
use MediaWiki\Html\FormOptions;
use MediaWiki\Html\Html;
use MediaWiki\Html\TemplateParser;
use MediaWiki\SpecialPage\SpecialPage;
use function wfEscapeWikiText;

/**
 * Includable special page for generating graphs for statistics.
 *
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
class TranslationStatsSpecialPage extends SpecialPage {

	private const GRAPH_CONTAINER_ID = 'translationStatsGraphContainer';
	private const GRAPH_CONTAINER_CLASS = 'mw-translate-translationstats-container';
	private readonly TemplateParser $templateParser;

	public function __construct(
		private readonly TranslationStatsDataProvider $dataProvider,
	) {
		parent::__construct( 'TranslationStats' );
		$this->templateParser = new TemplateParser( __DIR__ . '/templates/' );
	}

	/** @inheritDoc */
	public function isIncludable(): bool {
		return true;
	}

	/** @inheritDoc */
	protected function getGroupName(): string {
		return 'translation';
	}

	/** @inheritDoc */
	public function execute( $par ): void {
		$graphOpts = new TranslationStatsGraphOptions();
		$graphOpts->bindArray( $this->getRequest()->getValues() );

		$pars = explode( ';', (string)$par );
		foreach ( $pars as $item ) {
			if ( !str_contains( $item, '=' ) ) {
				continue;
			}

			[ $key, $value ] = array_map( 'trim', explode( '=', $item, 2 ) );
			if ( $graphOpts->hasValue( $key ) ) {
				$graphOpts->setValue( $key, $value );
			}
		}

		$graphOpts->normalize( $this->dataProvider->getGraphTypes() );
		$opts = $graphOpts->getFormOptions();

		if ( $this->including() ) {
			$this->getOutput()->addHTML( $this->embed( $opts ) );
		} else {
			$this->form( $opts );
		}
	}

	/**
	 * Constructs the form which can be used to generate custom graphs.
	 *
	 * @suppress SecurityCheck-DoubleEscaped Intentionally outputting what user should type
	 */
	private function form( FormOptions $opts ): void {
		$this->setHeaders();
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.translationstats' );
		$out->addModuleStyles( [
			'ext.translate.special.translationstats.styles',
			'codex-styles'
		] );
		$out->addHelpLink( 'Help:Extension:Translate/Statistics_and_reporting' );
		$out->addWikiMsg( 'translate-statsf-intro' );

		$out->addHTML(
			Html::errorBox(
				$this->msg( 'tux-nojs' )->escaped(),
				'',
				'tux-nojs'
			)
		);

		$now = new DateTime( 'now', new DateTimeZone( 'UTC' ) );

		$scaleOptions = [];
		foreach ( [ 'years', 'months', 'weeks', 'days', 'hours' ] as $scale ) {
			$scaleOptions[] = [
				'label' => $this->msg( "translate-statsf-scale-$scale" )->text(),
				'value' => $scale,
				'selected' => $scale === $opts['scale']
			];
		}

		$countOptions = [];
		foreach ( $this->dataProvider->getGraphTypes() as $count ) {
			$countOptions[] = [
				'label' => $this->msg( "translate-statsf-count-$count" )->text(),
				'value' => $count,
				'selected' => $count === $opts['count']
			];
		}

		$data = [
			'action' => $this->getConfig()->get( 'Script' ),
			'pageTitle' => $this->getPageTitle()->getPrefixedText(),
			'widthLabel' => $this->msg( 'translate-statsf-width' )->text(),
			'width' => $opts['width'],
			'widthMin' => TranslationStatsGraphOptions::INT_BOUNDS['width']['min'],
			'widthMax' => TranslationStatsGraphOptions::INT_BOUNDS['width']['max'],
			'heightLabel' => $this->msg( 'translate-statsf-height' )->text(),
			'height' => $opts['height'],
			'heightMin' => TranslationStatsGraphOptions::INT_BOUNDS['height']['min'],
			'heightMax' => TranslationStatsGraphOptions::INT_BOUNDS['height']['max'],
			'startLabel' => $this->msg( 'translate-statsf-start' )->text(),
			'start' => $opts['start'],
			'maxDate' => $now->format( 'Y-m-d' ),
			'daysLabel' => $this->msg( 'translate-statsf-days' )->text(),
			'days' => $opts['days'],
			'daysMin' => TranslationStatsGraphOptions::INT_BOUNDS['days']['min'],
			'daysMax' => TranslationStatsGraphOptions::INT_BOUNDS['days']['max'],
			'scaleLabel' => $this->msg( 'translate-statsf-scale' )->text(),
			'scaleOptions' => $scaleOptions,
			'countLabel' => $this->msg( 'translate-statsf-count' )->text(),
			'countOptions' => $countOptions,
			'languageLabel' => $this->msg( 'translate-statsf-language' )->text(),
			'language' => implode( ',', $opts['language'] ),
			'groupLabel' => $this->msg( 'translate-statsf-group' )->text(),
			'group' => implode( ',', $opts['group'] ),
			'submitLabel' => $this->msg( 'translate-statsf-submit' )->text(),
		];

		$out->addHTML(
		Html::openElement( 'fieldset', [ 'class' => 'mw-translate-stats-form' ] ) .
			$this->templateParser->processTemplate( 'TranslationStatsForm', $data ) .
			Html::closeElement( 'fieldset' )
		);

		if ( !$opts['preview'] ) {
			return;
		}
		$spiParams = [];
		foreach ( $opts->getChangedValues() as $key => $v ) {
			if ( $key === 'preview' ) {
				continue;
			}
			if ( is_array( $v ) ) {
				$v = implode( ',', $v );
				if ( !strlen( $v ) ) {
					continue;
				}
			}
			$spiParams[] = $key . '=' . wfEscapeWikiText( $v );
		}

		$spiParams = $spiParams ? '/' . implode( ';', $spiParams ) : '';

		$titleText = $this->getPageTitle()->getPrefixedText();
		$out->addHTML(
		Html::openElement( 'div', [ 'class' => 'mw-translate-stats-preview' ] ) .
		Html::element( 'hr' ) .
		// Element to render the graph
		Html::rawElement(
			'div',
			[
				'id' => self::GRAPH_CONTAINER_ID,
				'style' => 'margin: 2em auto; display: block',
				'class' => self::GRAPH_CONTAINER_CLASS,
			]
		) .
		Html::element(
			'pre',
			[ 'aria-label' => $this->msg( 'translate-statsf-embed' )->text() ],
			"{{{$titleText}{$spiParams}}}"
		) .
		Html::closeElement( 'div' )
		);
	}

	private function embed( FormOptions $opts ): string {
		$this->getOutput()->addModules( 'ext.translate.translationstats.embedded' );
		return Html::rawElement(
			'div',
			[
				'class' => self::GRAPH_CONTAINER_CLASS,
			],
			Html::hidden(
				'translationStatsGraphOptions',
				json_encode( $opts->getAllValues() )
			)
		);
	}
}
