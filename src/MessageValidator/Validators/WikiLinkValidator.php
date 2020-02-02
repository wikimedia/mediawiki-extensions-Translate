<?php
/**
 * @file
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

use MediaWiki\Extensions\Translate\MessageValidator\Validator;
use Title;
use TMessage;

/**
 * Checks if the translation uses links that are discouraged. Valid links are those that link
 * to Special: or {{ns:special}}: or project pages trough MediaWiki messages like
 * {{MediaWiki:helppage-url}}:. Also links in the definition are allowed.
 * @since 2020.02
 */
class WikiLinkValidator implements Validator {
	public function validate( TMessage $message, $code, array &$notices ) {
		$tc = Title::legalChars() . '#%{}';

		$key = $message->key();
		$definition = $message->definition();
		$translation = $message->translation();

		$subcheck = 'extra';
		$matches = $links = [];
		preg_match_all( "/\[\[([{$tc}]+)(\\|(.+?))?]]/sDu", $translation, $matches );
		$count = count( $matches[0] );
		for ( $i = 0; $i < $count; $i++ ) {
			$backMatch = preg_quote( $matches[1][$i], '/' );

			if ( preg_match( "/\[\[$backMatch/", $definition ) ) {
				continue;
			}

			$links[] = "[[{$matches[1][$i]}{$matches[2][$i]}]]";
		}

		if ( count( $links ) ) {
			$notices[$key][] = [
				[ 'links', $subcheck, $key, $code ],
				'translate-checks-links',
				[ 'PARAMS', $links ],
				[ 'COUNT', count( $links ) ],
			];
		}

		$subcheck = 'missing';
		$matches = $links = [];
		preg_match_all( "/\[\[([{$tc}]+)(\\|(.+?))?]]/sDu", $definition, $matches );
		$count = count( $matches[0] );
		for ( $i = 0; $i < $count; $i++ ) {
			$backMatch = preg_quote( $matches[1][$i], '/' );

			if ( preg_match( "/\[\[$backMatch/", $translation ) ) {
				continue;
			}

			$links[] = "[[{$matches[1][$i]}{$matches[2][$i]}]]";
		}

		if ( count( $links ) ) {
			$notices[$key][] = [
				[ 'links', $subcheck, $key, $code ],
				'translate-checks-links-missing',
				[ 'PARAMS', $links ],
				[ 'COUNT', count( $links ) ],
			];
		}
	}
}
