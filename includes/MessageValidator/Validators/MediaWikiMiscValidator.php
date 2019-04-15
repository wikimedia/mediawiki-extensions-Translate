<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

use MediaWiki\Extensions\Translate\MessageValidator\Validator;

/**
 * Handles misc validations for MediaWiki
 * @since 2019.04
 */
class MediaWikiMiscValidator implements Validator {
	public function validate( $messages, $code, &$notices ) {
		$timeList = [ 'protect-expiry-options', 'ipboptions' ];

		foreach ( $messages as $message ) {
			$key = $message->key();
			$definition = $message->definition();
			$translation = $message->translation();

			if ( in_array( strtolower( $key ), $timeList, true ) ) {
				$defArray = explode( ',', $definition );
				$traArray = explode( ',', $translation );

				$subcheck = 'timelist-count';
				$defCount = count( $defArray );
				$traCount = count( $traArray );
				if ( $defCount !== $traCount ) {
					$notices[$key][] = [
						[ 'miscmw', $subcheck, $key, $code ],
						'translate-checks-format',
						wfMessage( 'translate-checks-parametersnotequal' )
							->numParams( $traCount, $defCount )->text()
					];

					continue;
				}

				for ( $i = 0; $i < $defCount; $i++ ) {
					$defItems = array_map( 'trim', explode( ':', $defArray[$i] ) );
					$traItems = array_map( 'trim', explode( ':', $traArray[$i] ) );

					$subcheck = 'timelist-format';
					if ( count( $traItems ) !== 2 ) {
						$notices[$key][] = [
							[ 'miscmw', $subcheck, $key, $code ],
							'translate-checks-format',
							wfMessage(
								'translate-checks-malformed',
								$traArray[$i]
							)->text()
						];
						continue;
					}

					$subcheck = 'timelist-format-value';
					if ( $traItems[1] !== $defItems[1] ) {
						$notices[$key][] = [
							[ 'miscmw', $subcheck, $key, $code ],
							'translate-checks-format',
							"<samp><nowiki>$traItems[1] !== $defItems[1]</nowiki></samp>", // @todo FIXME: i18n missing.
						];
						continue;
					}
				}
			}
		}
	}
}
