<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

use MediaWiki\Extensions\Translate\MessageValidator\Validator;
use TMessage;

/**
 * Ensures that translations do not translate namespaces.
 * @since 2020.02
 */
class MediaWikiPageNameValidator implements Validator {
	public function validate( TMessage $message, $code, array &$notices ) {
		$key = $message->key();
		$definition = $message->definition();
		$translation = $message->translation();

		$subcheck = 'namespace';
		$namespaces = 'help|project|\{\{ns:project}}|mediawiki';
		$matches = [];
		if ( preg_match( "/^($namespaces):[\w\s]+$/ui", $definition, $matches ) &&
			!preg_match( "/^{$matches[1]}:.+$/u", $translation )
		) {
			$notices[$key][] = [
				[ 'pagename', $subcheck, $key, $code ],
				'translate-checks-pagename',
			];
		}
	}
}
