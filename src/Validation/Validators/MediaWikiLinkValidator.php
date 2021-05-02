<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use Title;
use TMessage;

/**
 * Checks if the translation uses links that are discouraged. Valid links are those that link
 * to Special: or {{ns:special}}: or project pages trough MediaWiki messages like
 * {{MediaWiki:helppage-url}}:. Also links in the definition are allowed.
 * @license GPL-2.0-or-later
 * @since 2020.02
 */
class MediaWikiLinkValidator implements MessageValidator {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();

		$definition = $message->definition();
		$translation = $message->translation();

		$links = $this->getLinksMissingInTarget( $definition, $translation );
		if ( $links !== [] ) {
			$issue = new ValidationIssue(
				'links',
				'missing',
				'translate-checks-links-missing',
				[
					[ 'PARAMS', $links ],
					[ 'COUNT', count( $links ) ],
				]
			);
			$issues->add( $issue );
		}

		$links = $this->getLinksMissingInTarget( $translation, $definition );
		if ( $links !== [] ) {
			$issue = new ValidationIssue(
				'links',
				'extra',
				'translate-checks-links',
				[
					[ 'PARAMS', $links ],
					[ 'COUNT', count( $links ) ],
				]
			);
			$issues->add( $issue );
		}

		return $issues;
	}

	private function getLinksMissingInTarget( string $source, string $target ): array {
		$tc = Title::legalChars() . '#%{}';
		$matches = $links = [];

		preg_match_all( "/\[\[([{$tc}]+)(\\|(.+?))?]]/sDu", $source, $matches );
		$count = count( $matches[0] );
		for ( $i = 0; $i < $count; $i++ ) {
			$backMatch = preg_quote( $matches[1][$i], '/' );
			if ( preg_match( "/\[\[$backMatch/", $target ) !== 1 ) {
				$links[] = "[[{$matches[1][$i]}{$matches[2][$i]}]]";
			}
		}

		return $links;
	}
}
