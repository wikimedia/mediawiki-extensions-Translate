<?php
/**
 * Contains logic for special page Special:TranslationStats.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Statistics\TranslationStatsGraphOptions;

/**
 * @defgroup Stats Statistics
 * Collection of code to produce various kinds of statistics.
 */

/**
 * Includable special page for generating graphs on translations.
 *
 * @ingroup SpecialPage TranslateSpecialPage Stats
 */
class SpecialTranslationStats extends SpecialPage {

	/** @var \MediaWiki\Extension\Translate\Statistics\TranslationStatsDataProvider */
	private $dataProvider;

	private const GRAPH_CONTAINER_ID = 'translationStatsGraphContainer';

	private const GRAPH_CONTAINER_CLASS = 'mw-translate-translationstats-container';

	public function __construct() {
		parent::__construct( 'TranslationStats' );
		$this->dataProvider = Services::getInstance()->getTranslationStatsDataProvider();
	}

	public function isIncludable() {
		return true;
	}

	protected function getGroupName() {
		return 'translation';
	}

	public function execute( $par ) {
		$graphOpts = new TranslationStatsGraphOptions();
		$graphOpts->bindArray( $this->getRequest()->getValues() );

		$pars = explode( ';', $par );
		foreach ( $pars as $item ) {
			if ( strpos( $item, '=' ) === false ) {
				continue;
			}

			list( $key, $value ) = array_map( 'trim', explode( '=', $item, 2 ) );
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
	 * @param FormOptions $opts
	 * @suppress SecurityCheck-DoubleEscaped Intentionally outputting what user should type
	 */
	protected function form( FormOptions $opts ) {
		global $wgScript;
		$this->setHeaders();
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.translationstats' );
		$out->addHelpLink( 'Help:Extension:Translate/Statistics_and_reporting' );
		$out->addWikiMsg( 'translate-statsf-intro' );
		$out->addHTML(
			Xml::fieldset( $this->msg( 'translate-statsf-options' )->text() ) .
				Html::openElement( 'form', [ 'action' => $wgScript, 'id' => 'translationStatsConfig' ] ) .
				Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() ) .
				Html::hidden( 'preview', 1 ) .
				'<table>'
		);
		$submit = Xml::submitButton( $this->msg( 'translate-statsf-submit' )->text() );
		$out->addHTML(
			$this->eInput( 'width', $opts ) .
				$this->eInput( 'height', $opts ) .
				'<tr><td colspan="2"><hr /></td></tr>' .
				$this->eInput( 'start', $opts, 24 ) .
				$this->eInput( 'days', $opts ) .
				$this->eRadio( 'scale', $opts, [ 'months', 'weeks', 'days', 'hours' ] ) .
				$this->eRadio( 'count', $opts, $this->dataProvider->getGraphTypes() ) .
				'<tr><td colspan="2"><hr /></td></tr>' .
				$this->eLanguage( 'language', $opts ) .
				$this->eGroup( 'group', $opts ) .
				'<tr><td colspan="2"><hr /></td></tr>' .
				'<tr><td colspan="2">' . $submit . '</td></tr>'
		);
		$out->addHTML(
			'</table>' .
				'</form>' .
				'</fieldset>'
		);
		if ( !$opts['preview'] ) {
			return;
		}
		$spiParams = '';
		foreach ( $opts->getChangedValues() as $key => $v ) {
			if ( $key === 'preview' ) {
				continue;
			}
			if ( $spiParams !== '' ) {
				$spiParams .= ';';
			}

			if ( is_array( $v ) ) {
				$v = implode( ',', $v );
				if ( !strlen( $v ) ) {
					continue;
				}
			}
			$spiParams .= wfEscapeWikiText( "$key=$v" );
		}
		if ( $spiParams !== '' ) {
			$spiParams = '/' . $spiParams;
		}
		$titleText = $this->getPageTitle()->getPrefixedText();
		$out->addHTML(
			Html::element( 'hr' )
		);
		// Element to render the graph
		$out->addHTML(
			Html::rawElement(
				'div',
				[
					'id' => self::GRAPH_CONTAINER_ID ,
					'style' => 'margin: 2em auto; display: block',
					'class' => self::GRAPH_CONTAINER_CLASS
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

	/**
	 * Constructs a table row with label and input in two columns.
	 * @param string $name Option name.
	 * @param FormOptions $opts
	 * @param int $width
	 * @return string Html.
	 */
	protected function eInput( $name, FormOptions $opts, $width = 4 ) {
		$value = $opts[$name];
		return '<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			Xml::input( $name, $width, $value, [ 'id' => $name ] ) .
			'</td></tr>' . "\n";
	}

	/**
	 * Constructs a label for option.
	 * @param string $name Option name.
	 * @return string Html.
	 */
	protected function eLabel( $name ) {
		// Give grep a chance to find the usages:
		// translate-statsf-width, translate-statsf-height, translate-statsf-start,
		// translate-statsf-days, translate-statsf-scale, translate-statsf-count,
		// translate-statsf-language, translate-statsf-group
		$label = 'translate-statsf-' . $name;
		$label = $this->msg( $label )->escaped();
		return Xml::tags( 'label', [ 'for' => $name ], $label );
	}

	/**
	 * Constructs a table row with label and radio input in two columns.
	 * @param string $name Option name.
	 * @param FormOptions $opts
	 * @param string[] $alts List of alternatives.
	 * @return string Html.
	 */
	protected function eRadio( $name, FormOptions $opts, array $alts ) {
		// Give grep a chance to find the usages:
		// translate-statsf-scale, translate-statsf-count
		$label = 'translate-statsf-' . $name;
		$label = $this->msg( $label )->escaped();
		$s = '<tr><td>' . $label . '</td><td>';
		$options = [];
		foreach ( $alts as $alt ) {
			$id = "$name-$alt";
			$radio = Xml::radio( $name, $alt, $alt === $opts[$name],
				[ 'id' => $id ] ) . ' ';
			$options[] = $radio . ' ' . $this->eLabel( $id );
		}
		$s .= implode( ' ', $options );
		$s .= '</td></tr>' . "\n";
		return $s;
	}

	/**
	 * Constructs a table row with label and language selector in two columns.
	 * @param string $name Option name.
	 * @param FormOptions $opts
	 * @return string Html.
	 */
	protected function eLanguage( $name, FormOptions $opts ) {
		$value = implode( ',', $opts[$name] );

		$select = $this->languageSelector();
		$select->setTargetId( 'language' );
		return '<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			$select->getHtmlAndPrepareJS() . '<br />' .
			Xml::input( $name, 20, $value, [ 'id' => $name ] ) .
			'</td></tr>' . "\n";
	}

	/**
	 * Constructs a JavaScript enhanced language selector.
	 * @return JsSelectToInput
	 */
	protected function languageSelector() {
		$languages = TranslateUtils::getLanguageNames( $this->getLanguage()->getCode() );
		ksort( $languages );
		$selector = new XmlSelect( 'mw-language-selector', 'mw-language-selector' );
		foreach ( $languages as $code => $name ) {
			$selector->addOption( "$code - $name", $code );
		}
		$jsSelect = new JsSelectToInput( $selector );
		return $jsSelect;
	}

	/**
	 * Constructs a table row with label and group selector in two columns.
	 * @param string $name Option name.
	 * @param FormOptions $opts
	 * @return string Html.
	 */
	protected function eGroup( $name, FormOptions $opts ) {
		$value = implode( ',', $opts[$name] );

		$select = $this->groupSelector();
		$select->setTargetId( 'group' );
		return '<tr><td>' . $this->eLabel( $name ) . '</td><td>' .
			$select->getHtmlAndPrepareJS() . '<br />' .
			Xml::input( $name, 20, $value, [ 'id' => $name ] ) .
			'</td></tr>' . "\n";
	}

	/**
	 * Constructs a JavaScript enhanced group selector.
	 * @return JsSelectToInput
	 */
	protected function groupSelector() {
		$groups = MessageGroups::singleton()->getGroups();
		/** @var MessageGroup $group */
		foreach ( $groups as $key => $group ) {
			if ( !$group->exists() ) {
				unset( $groups[$key] );
				continue;
			}
		}
		ksort( $groups );
		$selector = new XmlSelect( 'mw-group-selector', 'mw-group-selector' );
		/** @var MessageGroup $name */
		foreach ( $groups as $code => $name ) {
			$selector->addOption( $name->getLabel(), $code );
		}
		$jsSelect = new JsSelectToInput( $selector );
		return $jsSelect;
	}

	protected function embed( FormOptions $opts ) {
		$this->getOutput()->addModules( 'ext.translate.translationstats.embedded' );
		return Html::rawElement(
			'div',
			[
				'class' => self::GRAPH_CONTAINER_CLASS
			],
			Html::hidden(
				'translationStatsGraphOptions',
				json_encode( $opts->getAllValues() )
			)
		);
	}
}
