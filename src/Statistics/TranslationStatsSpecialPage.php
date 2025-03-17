<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use DateTime;
use DateTimeZone;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Utilities\JsSelectToInput;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Html\FormOptions;
use MediaWiki\Html\Html;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\Xml\Xml;
use MediaWiki\Xml\XmlSelect;
use function wfEscapeWikiText;

/**
 * Includable special page for generating graphs for statistics.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
class TranslationStatsSpecialPage extends SpecialPage {
	private TranslationStatsDataProvider $dataProvider;
	private const GRAPH_CONTAINER_ID = 'translationStatsGraphContainer';
	private const GRAPH_CONTAINER_CLASS = 'mw-translate-translationstats-container';

	public function __construct( TranslationStatsDataProvider $dataProvider ) {
		parent::__construct( 'TranslationStats' );
		$this->dataProvider = $dataProvider;
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
		$script = $this->getConfig()->get( 'Script' );

		$this->setHeaders();
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.translationstats' );
		$out->addHelpLink( 'Help:Extension:Translate/Statistics_and_reporting' );
		$out->addWikiMsg( 'translate-statsf-intro' );
		$out->addHTML(
			Html::openElement( 'fieldset' ) .
			Html::element( 'legend', [], $this->msg( 'translate-statsf-options' )->text() ) .
			Html::openElement(
				'form',
				[ 'action' => $script, 'id' => 'translationStatsConfig' ]
			) .
			Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
			Html::hidden( 'preview', 1 ) . '<table>'
		);
		$now = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$submit = Html::submitButton( $this->msg( 'translate-statsf-submit' )->text() );
		$out->addHTML(
			$this->eNumber( 'width', $opts ) . $this->eNumber( 'height', $opts ) .
			'<tr><td colspan="2"><hr /></td></tr>' .
			$this->eInput( 'start', $opts, 'date', [ 'max' => $now->format( 'Y-m-d' ) ] ) .
			$this->eNumber( 'days', $opts ) .
			$this->eRadio( 'scale', $opts, [ 'years', 'months', 'weeks', 'days', 'hours' ] ) .
			$this->eRadio( 'count', $opts, $this->dataProvider->getGraphTypes() ) .
			'<tr><td colspan="2"><hr /></td></tr>' . $this->eLanguage( 'language', $opts ) .
			$this->eGroup( 'group', $opts ) . '<tr><td colspan="2"><hr /></td></tr>' .
			'<tr><td colspan="2">' . $submit . '</td></tr>'
		);
		$out->addHTML( '</table></form></fieldset>' );
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
		$out->addHTML( Html::element( 'hr' ) );
		// Element to render the graph
		$out->addHTML(
			Html::rawElement(
				'div',
				[
					'id' => self::GRAPH_CONTAINER_ID,
					'style' => 'margin: 2em auto; display: block',
					'class' => self::GRAPH_CONTAINER_CLASS,
				]
			)
		);

		$out->addHTML(
			Html::element(
				'pre',
				[ 'aria-label' => $this->msg( 'translate-statsf-embed' )->text() ],
				"{{{$titleText}{$spiParams}}}"
			)
		);
	}

	/** Construct HTML for a table row with label and number input in two columns. */
	private function eNumber( string $name, FormOptions $opts ): string {
		return $this->eInput( $name, $opts, 'number', TranslationStatsGraphOptions::INT_BOUNDS[$name] );
	}

	/** Construct HTML for a table row with label and a specified type of input in two columns. */
	private function eInput( string $name, FormOptions $opts, string $type, array $attribs ): string {
		$value = $opts[$name];
		return '<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			Html::input( $name, $value, $type, [ 'id' => $name ] + $attribs ) .
			'</td></tr>' . "\n";
	}

	/// Construct HTML for a label for option.
	private function eLabel( string $name ): string {
		// Give grep a chance to find the usages:
		// translate-statsf-width, translate-statsf-height, translate-statsf-start,
		// translate-statsf-days, translate-statsf-scale, translate-statsf-count,
		// translate-statsf-language, translate-statsf-group
		$label = 'translate-statsf-' . $name;
		$label = $this->msg( $label )->escaped();
		return Xml::tags( 'label', [ 'for' => $name ], $label );
	}

	/// Construct HTML for a table row with label and radio input in two columns.
	private function eRadio( string $name, FormOptions $opts, array $alts ): string {
		// Give grep a chance to find the usages:
		// translate-statsf-scale, translate-statsf-count
		$label = 'translate-statsf-' . $name;
		$label = $this->msg( $label )->escaped();
		$s = '<tr><td>' . $label . '</td><td>';
		$options = [];
		foreach ( $alts as $alt ) {
			$id = "$name-$alt";
			$radio = Html::radio(
				$name,
				$alt === $opts[$name],
				[
					'value' => $alt,
					'id' => $id
				]
			) . ' ';
			$options[] = $radio . ' ' . $this->eLabel( $id );
		}
		$s .= implode( ' ', $options );
		$s .= '</td></tr>' . "\n";
		return $s;
	}

	/// Construct HTML for a table row with label and language selector in two columns.
	private function eLanguage( string $name, FormOptions $opts ): string {
		$value = implode( ',', $opts[$name] );

		$select = $this->languageSelector();
		$select->setTargetId( 'language' );
		return '<tr><td>' . $this->eLabel( $name ) . '</td><td>' . $select->getHtmlAndPrepareJS() .
			'<br />' . Html::input( $name, $value, 'text', [ 'id' => $name, 'size' => 20 ] ) .
			'</td></tr>' . "\n";
	}

	/// Construct a JavaScript enhanced language selector.
	private function languageSelector(): JsSelectToInput {
		$languages = Utilities::getLanguageNames( $this->getLanguage()->getCode() );
		ksort( $languages );
		$selector = new XmlSelect( 'mw-language-selector', 'mw-language-selector' );
		foreach ( $languages as $code => $name ) {
			$selector->addOption( "$code - $name", $code );
		}
		return new JsSelectToInput( $selector );
	}

	/// Constructs HTML for a table row with label and group selector in two columns.
	private function eGroup( string $name, FormOptions $opts ): string {
		$value = implode( ',', $opts[$name] );

		$select = $this->groupSelector();
		$select->setTargetId( 'group' );
		return '<tr><td>' . $this->eLabel( $name ) . '</td><td>' . $select->getHtmlAndPrepareJS() .
			'<br />' . Html::input( $name, $value, 'text', [ 'id' => $name, 'size' => 20 ] ) .
			'</td></tr>' . "\n";
	}

	/// Construct a JavaScript enhanced group selector.
	private function groupSelector(): JsSelectToInput {
		$groups = MessageGroups::singleton()->getGroups();
		foreach ( $groups as $key => $group ) {
			if ( !$group->exists() ) {
				unset( $groups[$key] );
			}
		}
		ksort( $groups );
		$selector = new XmlSelect( 'mw-group-selector', 'mw-group-selector' );
		foreach ( $groups as $code => $name ) {
			$selector->addOption( $name->getLabel(), $code );
		}
		return new JsSelectToInput( $selector );
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
