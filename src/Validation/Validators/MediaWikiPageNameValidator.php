<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation\Validators;

use MediaWiki\Extension\Translate\Validation\MessageValidator;
use MediaWiki\Extension\Translate\Validation\ValidationIssue;
use MediaWiki\Extension\Translate\Validation\ValidationIssues;
use TMessage;

/**
 * Ensures that translations do not translate namespaces.
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2020.02
 */
class MediaWikiPageNameValidator implements MessageValidator {
	public function getIssues( TMessage $message, string $targetLanguage ): ValidationIssues {
		$issues = new ValidationIssues();

		$definition = $message->definition();
		$translation = $message->translation();

		$namespaces = 'help|project|\{\{ns:project}}|mediawiki';
		$matches = [];
		if ( preg_match( "/^($namespaces):[\w\s]+$/ui", $definition, $matches ) &&
			!preg_match( "/^{$matches[1]}:.+$/u", $translation )
		) {
			$issue = new ValidationIssue(
				'pagename',
				'namespace',
				'translate-checks-pagename'
			);
			$issues->add( $issue );
		}

		return $issues;
	}
}
