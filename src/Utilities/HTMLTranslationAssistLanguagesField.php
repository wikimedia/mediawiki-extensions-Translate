<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use MediaWiki\HTMLForm\Field\HTMLSelectLanguageField;

/**
 * Handle formatting between the legacy string format stored in the database
 * and the array format expected by the HTMLSelectLanguageField widget.
 *
 * @author Huei Tan
 * @since 2026.04
 * @license GPL-2.0-or-later
 */
class HTMLTranslationAssistLanguagesField extends HTMLSelectLanguageField {

	/** @inheritDoc */
	public function getInputCodex( $value, $hasErrors ) {
		$this->mParent->getOutput()->addModuleStyles( 'ext.translate.translationassistlanguagesfield.styles' );
		return parent::getInputCodex( $value, $hasErrors );
	}

	/** @inheritDoc */
	public function validate( $value, $alldata ) {
		// Bypass validation for the legacy database token "default"
		if ( $value === 'default' ) {
			return true;
		}

		if ( is_string( $value ) ) {
			$value = array_map( 'trim', explode( ',', $value ) );
			$value = array_unique( array_filter( $value ) );
		}

		return parent::validate( $value, $alldata );
	}

	/** @inheritDoc */
	public function filter( $value, $alldata ) {
		$val = parent::filter( $value, $alldata );

		if ( is_array( $val ) ) {
			$validValues = array_filter( $val, 'is_string' );
			return implode( ',', $validValues );
		}

		return $val;
	}
}
