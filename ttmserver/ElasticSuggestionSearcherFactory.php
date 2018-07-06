<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @copyright Copyright © 2018, Wikimedia Foundation
 * @license GPL-2.0-or-later
 * @ingroup TTMServer
 */

/**
 * Simple factory of ElasticSuggestionSearcher
 * @see ElasticSuggestionSearcher
 * @since 2018.07
 * @ingroup TTMServer
 */
class ElasticSuggestionSearcherFactory {

	const PROFILE_KEY = 'suggestion_searcher_profile';
	const PROFILES_KEY = 'suggestion_searcher_profiles';
	const URI_PARAM_OVERRIDE = 'ttmElasticSuggestionProfile';

	public static function getSuggestionSearcher(
		ElasticSearchTTMServer $ttmServer,
		WebRequest $request = null
	) {
		$request = $request ?? RequestContext::getMain()->getRequest();
		$config = $ttmServer->getConfig();
		$profileName = $request->getVal( self::URI_PARAM_OVERRIDE,
			$config[self::PROFILE_KEY] ?? 'classic' );
		$searcherSetup = $config[self::PROFILES_KEY][$profileName] ?? [ 'type' => $profileName ];
		$searcherSetup += [ 'type' => 'classic', 'params' => [] ];
		switch ( $searcherSetup['type'] ) {
		case 'classic':
			return new ElasticClassicSuggestionSearcher( $ttmServer );
		case 'rescoring':
			return new ElasticRescoringSuggestionSearcher( $ttmServer,
				$searcherSetup['params'], $request );
		}
		throw new \RuntimeException( 'Unknown ElasticSuggestionSearcher type: ['
			. $searcherSetup['type'] . ']' );
	}
}
