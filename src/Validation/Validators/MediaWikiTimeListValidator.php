<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use TMessage;

/**
 * "Time list" message format validation for MediaWiki.
 *
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2019.06
 */
class MediaWikiTimeListValidator implements MessageValidator {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();

		$definition = $message->definition();
		$translation = $message->translation();
		$defArray = explode( ',', $definition );
		$traArray = explode( ',', $translation );

		$defCount = count( $defArray );
		$traCount = count( $traArray );
		if ( $defCount !== $traCount ) {
			$issue = new ValidationIssue(
				'miscmw',
				'timelist-count',
				'translate-checks-format',
				[
					[
						'MESSAGE',
						[
							'translate-checks-parametersnotequal',
							[ 'COUNT', $traCount ],
							[ 'COUNT', $defCount ],
						]
					]
				]
			);
			$issues->add( $issue );

			return $issues;
		}

		for ( $i = 0; $i < $defCount; $i++ ) {
			$defItems = array_map( 'trim', explode( ':', $defArray[$i] ) );
			$traItems = array_map( 'trim', explode( ':', $traArray[$i] ) );

			if ( count( $traItems ) !== 2 ) {
				$issue = new ValidationIssue(
					'miscmw',
					'timelist-format',
					'translate-checks-format',
					[ [ 'MESSAGE', [ 'translate-checks-malformed', $traArray[$i] ] ] ]
				);

				$issues->add( $issue );
				continue;
			}

			if ( $traItems[1] !== $defItems[1] ) {
				$issue = new ValidationIssue(
					'miscmw',
					'timelist-format-value',
					'translate-checks-format',
					// FIXME: i18n missing.
					[ "<samp><nowiki>$traItems[1] !== $defItems[1]</nowiki></samp>" ]
				);

				$issues->add( $issue );
			}
		}

		return $issues;
	}
}
