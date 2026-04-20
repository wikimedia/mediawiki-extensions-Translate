<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use MediaWiki\Config\Config;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\CLDR\LanguageNames;
use MediaWiki\Extension\Translate\ConfigNames;
use MediaWiki\Html\Html;
use MediaWiki\Language\LanguageNameUtils;
use MediaWiki\RecentChanges\Hook\FetchChangesListHook;
use MediaWiki\SpecialPage\Hook\ChangesListSpecialPageQueryHook;
use MediaWiki\Specials\Hook\SpecialRecentChangesPanelHook;
use Wikimedia\Rdbms\IConnectionProvider;

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

	public function __construct(
		private readonly LanguageNameUtils $languageNameUtils,
		private readonly IConnectionProvider $dbProvider,
		private readonly Config $config,
	) {
	}

	/**
	 * Hook: ChangesListSpecialPageQuery
	 * @inheritDoc
	 */
	public function onChangesListSpecialPageQuery(
		$name, &$tables, &$fields, &$conds, &$query_options, &$join_conds, $opts
	) {
		if ( !$this->config->get( ConfigNames::RecentChangesLanguageFilter ) ) {
			return;
		}

		$opts->add( 'trailer', '' );
		$trailer = RequestContext::getMain()->getRequest()->getVal( 'trailer', '' );
		if ( $trailer === '' ) {
			return;
		}

		$dbr = $this->dbProvider->getReplicaDatabase();
		$conds[] = 'rc_title ' . $dbr->buildLike( $dbr->anyString(), $trailer );
		$opts->setValue( 'trailer', $trailer );
	}

	/**
	 * Hook: SpecialRecentChangesPanel
	 * @inheritDoc
	 */
	public function onSpecialRecentChangesPanel( &$extraOpts, $opts ) {
		if ( !$this->config->get( ConfigNames::RecentChangesLanguageFilter ) ) {
			return;
		}

		$context = RequestContext::getMain();
		// TODO the query is parsed (and unknown options are discarded) before we got a chance to define our option.
		// SEE https://gerrit.wikimedia.org/r/c/mediawiki/extensions/Translate/+/1053288/comment/5303b6a3_aaef9399/
		$default = $context->getRequest()->getVal( 'trailer', '' );

		if ( is_callable( [ LanguageNames::class, 'getNames' ] ) ) {
			// cldr extension
			$languages = LanguageNames::getNames(
				$context->getLanguage()->getCode(),
				LanguageNames::FALLBACK_NORMAL
			);
		} else {
			$languages = $this->languageNameUtils->getLanguageNames();
		}
		ksort( $languages );

		$options = Html::element(
			'option',
			[
				'value' => '',
				'selected' => $default === '',
			],
			wfMessage( 'tpt-cleanchanges-language-na' )->text()
		);

		foreach ( $languages as $code => $name ) {
			$options .= Html::element(
				'option',
				[
					'value' => "/$code",
					'selected' => $default === "/$code",
				],
				"$code - $name"
			) . "\n";
		}

		$extraOpts['tailer'] = [
			wfMessage( 'tpt-cleanchanges-language' )->escaped(),
			Html::rawElement(
				'select',
				[
					'name' => 'trailer',
					'class' => 'mw-language-selector',
					'id' => 'tpt-rc-language',
				],
				$options
			),
		];
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
