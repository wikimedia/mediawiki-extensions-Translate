<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use MediaWiki\Config\Config;
use MediaWiki\Extension\CLDR\LanguageNames;
use MediaWiki\Extension\Translate\ConfigNames;
use MediaWiki\Hook\FetchChangesListHook;
use MediaWiki\Hook\SpecialRecentChangesPanelHook;
use MediaWiki\Html\Html;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\SpecialPage\Hook\ChangesListSpecialPageQueryHook;
use MediaWiki\Xml\Xml;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * This class adds a language filter to Special:RecentChanges
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2024.08
 */
class CleanChangesFilters implements
	FetchChangesListHook,
	ChangesListSpecialPageQueryHook,
	SpecialRecentChangesPanelHook
{
	private Config $config;
	private LanguageNameUtils $languageNameUtils;
	private ILoadBalancer $loadBalancer;

	public function __construct( LanguageNameUtils $languageNameUtils, ILoadBalancer $loadBalancer, Config $config ) {
		$this->languageNameUtils = $languageNameUtils;
		$this->loadBalancer = $loadBalancer;
		$this->config = $config;
	}

	/**
	 * Hook: ChangesListSpecialPageQuery
	 * @inheritDoc
	 */
	public function onChangesListSpecialPageQuery(
		$name, &$tables, &$fields, &$conds, &$query_options, &$join_conds, $opts
	) {
		global $wgRequest;
		if ( !$this->config->get( ConfigNames::RecentChangesLanguageFilter ) ) {
			return;
		}

		$opts->add( 'trailer', '' );
		$trailer = $wgRequest->getVal( 'trailer', '' );
		if ( $trailer === null ) {
			return;
		}

		$dbr = $this->loadBalancer->getConnection( DB_REPLICA );
		$conds[] = 'rc_title ' . $dbr->buildLike( $dbr->anyString(), $trailer );
		$opts->setValue( 'trailer', $trailer );
	}

	/**
	 * Hook: SpecialRecentChangesPanel
	 * @inheritDoc
	 */
	public function onSpecialRecentChangesPanel( &$extraOpts, $opts ) {
		global $wgLang, $wgRequest;
		if ( !$this->config->get( ConfigNames::RecentChangesLanguageFilter ) ) {
			return;
		}

		// TODO the query is parsed (and unknown options are discarded) before we got a chance to define our option.
		// SEE https://gerrit.wikimedia.org/r/c/mediawiki/extensions/Translate/+/1053288/comment/5303b6a3_aaef9399/
		$default = $wgRequest->getVal( 'trailer', '' );

		if ( is_callable( [ LanguageNames::class, 'getNames' ] ) ) {
			// cldr extension
			$languages = LanguageNames::getNames(
				$wgLang->getCode(),
				LanguageNames::FALLBACK_NORMAL
			);
		} else {
			$languages = $this->languageNameUtils->getLanguageNames();
		}
		ksort( $languages );
		$optionAttributes = [ 'value' => '' ];
		if ( $default === '' ) {
			$optionAttributes[ 'selected' ] = 'selected';
		}

		$options = Html::element(
			'option',
			$optionAttributes,
			wfMessage( 'tpt-cleanchanges-language-na' )->text()
		);

		foreach ( $languages as $code => $name ) {
			$selected = ( "/$code" === $default );
			$optionAttributes = [ 'value' => "/$code" ];
			if ( $selected ) {
				$optionAttributes[ 'selected' ] = 'selected';
			}
			$options .= Html::element( 'option', $optionAttributes, "$code - $name" ) . "\n";
		}
		$str =
		Xml::openElement( 'select',
			[
				'name' => 'trailer',
				'class' => 'mw-language-selector',
				'id' => 'tpt-rc-language',
			] ) .
		$options .
		Xml::closeElement( 'select' );

		$extraOpts['tailer'] = [ wfMessage( 'tpt-cleanchanges-language' )->escaped(), $str ];
	}

	/**
	 * Hook: FetchChangesList
	 * @inheritDoc
	 */
	public function onFetchChangesList( $user, $skin, &$list, $groups ) {
		if ( $this->config->get( ConfigNames::RecentChangesLanguageFilter ) && defined( 'ULS_VERSION' ) ) {
			$skin->getOutput()->addModules( 'ext.translate.cleanchanges' );
		}
	}
}
