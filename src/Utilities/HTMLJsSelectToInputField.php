<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use MediaWiki\HTMLForm\Field\HTMLTextField;

/**
 * Implementation of JsSelectToInput class which is compatible with MediaWiki's preferences system
 * @author Niklas Laxström
 * @copyright Copyright © 2010 Niklas Laxström
 * @license GPL-2.0-or-later
 */
class HTMLJsSelectToInputField extends HTMLTextField {
	/** @inheritDoc */
	public function getInputHTML( $value ): string {
		$input = parent::getInputHTML( $value );

		if ( isset( $this->mParams['select'] ) ) {
			/** @var JsSelectToInput $select */
			$select = $this->mParams['select'];
			$input = $select->getHtmlAndPrepareJS() . '<br />' . $input;
		}

		return $input;
	}

	/** @return string[] */
	protected function tidy( string $value ): array {
		$value = array_map( 'trim', explode( ',', $value ) );
		$value = array_unique( array_filter( $value ) );

		return $value;
	}

	/** @inheritDoc */
	public function validate( $value, $alldata ) {
		$p = parent::validate( $value, $alldata );

		if ( $p !== true ) {
			return $p;
		}

		if ( !isset( $this->mParams['valid-values'] ) ) {
			return true;
		}

		if ( $value === 'default' ) {
			return true;
		}

		$codes = $this->tidy( $value );
		$valid = array_flip( $this->mParams['valid-values'] );

		foreach ( $codes as $code ) {
			if ( !isset( $valid[$code] ) ) {
				return wfMessage( 'translate-pref-editassistlang-bad', $code )->parseAsBlock();
			}
		}

		return true;
	}

	/** @inheritDoc */
	public function filter( $value, $alldata ) {
		$value = parent::filter( $value, $alldata );

		return implode( ', ', $this->tidy( $value ) );
	}
}
