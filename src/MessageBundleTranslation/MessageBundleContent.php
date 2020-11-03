<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use JsonContent;
use Status;
use User;
use WikiPage;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class MessageBundleContent extends JsonContent {
	public const CONTENT_MODEL_ID = 'translate-messagebundle';

	public function __construct( $text, $modelId = self::CONTENT_MODEL_ID ) {
		parent::__construct( $text, $modelId );
	}

	public function isValid(): bool {
		try {
			return parent::isValid() && $this->validate();
		} catch ( MalformedBundle $e ) {
			return false;
		}
	}

	/** @throws MalformedBundle */
	public function validate(): bool {
		$data = json_decode( $this->getText(), true );

		// Crude check that we have an associative array (or empty array)
		if ( !is_array( $data ) || ( $data !== [] && array_values( $data ) === $data ) ) {
			throw new MalformedBundle(
				'translate-messagebundle-error-invalid-array',
				[ gettype( $data ) ]
			);
		}

		foreach ( $data as $key => $value ) {
			if ( $key === '' ) {
				throw new MalformedBundle( 'translate-messagebundle-error-key-empty' );
			}

			if ( strlen( $key ) > 100 ) {
				throw new MalformedBundle(
					'translate-messagebundle-error-key-too-long',
					[ $key ]
				);
			}

			if ( !preg_match( '/^[a-zA-Z0-9-_.]+$/', $key ) ) {
				throw new MalformedBundle(
					'translate-messagebundle-error-key-invalid-characters',
					[ $key ]
				);
			}

			if ( !is_string( $value ) ) {
				throw new MalformedBundle(
					'translate-messagebundle-error-invalid-value',
					[ $key ]
				);
			}

			if ( trim( $value ) === '' ) {
				throw new MalformedBundle(
					'translate-messagebundle-error-empty-value',
					[ $key ]
				);
			}
		}

		return true;
	}

	public function prepareSave( WikiPage $page, $flags, $parentRevId, User $user ) {
		// This will give an informative error message when trying to change the content model
		try {
			$this->validate();
			return Status::newGood();
		} catch ( MalformedBundle $e ) {
			// XXX: We have no context source nor is there Message::messageParam :(
			return Status::newFatal( 'translate-messagebundle-validation-error', wfMessage( $e ) );
		}
	}
}
