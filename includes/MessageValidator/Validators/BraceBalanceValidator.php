<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

use MediaWiki\Extensions\Translate\MessageValidator\Validator;
use MediaWiki\Extensions\Translate\MessageValidator\ValidationHelper;

/**
 * Handles brace balance validation
 * @since 2019.04
 */
class BraceBalanceValidator implements Validator {
	use ValidationHelper;

	public function validate( $messages, $code, &$notices ) {
		foreach ( $messages as $message ) {
			$key = $message->key();
			$translation = $message->translation();
			$translation = preg_replace( '/[^{}[\]()]/u', '', $translation );

			$subcheck = 'brace';
			$counts = [
				'{' => 0, '}' => 0,
				'[' => 0, ']' => 0,
				'(' => 0, ')' => 0,
			];

			$len = strlen( $translation );
			for ( $i = 0; $i < $len; $i++ ) {
				$char = $translation[$i];
				$counts[$char]++;
			}

			$definition = $message->definition();

			$balance = [];
			if ( $counts['['] !== $counts[']'] && self::checkStringCountEqual( $definition, '[', ']' ) ) {
				$balance[] = '[]: ' . ( $counts['['] - $counts[']'] );
			}

			if ( $counts['{'] !== $counts['}'] && self::checkStringCountEqual( $definition, '{', '}' ) ) {
				$balance[] = '{}: ' . ( $counts['{'] - $counts['}'] );
			}

			if ( $counts['('] !== $counts[')'] && self::checkStringCountEqual( $definition, '(', ')' ) ) {
				$balance[] = '(): ' . ( $counts['('] - $counts[')'] );
			}

			if ( count( $balance ) ) {
				$notices[$key][] = [
					[ 'balance', $subcheck, $key, $code ],
					'translate-checks-balance',
					[ 'PARAMS', $balance ],
					[ 'COUNT', count( $balance ) ],
				];
			}
		}
	}
}
