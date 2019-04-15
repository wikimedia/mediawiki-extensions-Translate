<?php
/**
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extensions\Translate\MessageValidator\Validators;

use MediaWiki\Extensions\Translate\MessageValidator\Validator;
use MediaWiki\Extensions\Translate\MessageValidator\ValidationHelper;
use TMessage;

/**
 * Handles brace balance validation
 * @since 2019.06
 */
class BraceBalanceValidator implements Validator {
	use ValidationHelper;

	public function validate( TMessage $message, $code, array &$notices ) {
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

		if ( $balance ) {
			$notices[$key][] = [
				[ 'balance', $subcheck, $key, $code ],
				'translate-checks-balance',
				[ 'PARAMS', $balance ],
				[ 'COUNT', count( $balance ) ],
			];
		}
	}
}
