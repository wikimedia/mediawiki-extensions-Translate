<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use MediaWiki\Content\JsonContent;
use MediaWiki\Json\FormatJson;
use MediaWiki\Message\Message;

/**
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2021.05
 */
class MessageBundleContent extends JsonContent {
	public const CONTENT_MODEL_ID = 'translate-messagebundle';
	// List of supported metadata keys
	/** @phpcs-require-sorted-array */
	public const METADATA_KEYS = [
		'allowOnlyPriorityLanguages',
		'description',
		'label',
		'priorityLanguages',
		'sourceLanguage'
	];
	private ?array $messages = null;
	private ?MessageBundleMetadata $metadata = null;

	/** @inheritDoc */
	public function __construct( $text, $modelId = self::CONTENT_MODEL_ID ) {
		parent::__construct( $text, $modelId );
	}

	public function isValid(): bool {
		try {
			$this->getMessages();
			$this->getMetadata();
			return parent::isValid();
		} catch ( MalformedBundle $e ) {
			return false;
		}
	}

	/** @throws MalformedBundle */
	public function validate(): void {
		$this->getMessages();
		$this->getMetadata();
	}

	/** @throws MalformedBundle */
	public function getMessages(): array {
		if ( $this->messages !== null ) {
			return $this->messages;
		}

		$data = $this->getRawData();
		// Remove the metadata since we are not concerned with it.
		unset( $data['@metadata'] );

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

		$this->messages = $data;
		return $this->messages;
	}

	public function getMetadata(): MessageBundleMetadata {
		if ( $this->metadata !== null ) {
			return $this->metadata;
		}

		$data = $this->getRawData();
		$metadata = $data['@metadata'] ?? [];

		if ( !is_array( $metadata ) ) {
			throw new MalformedBundle( 'translate-messagebundle-error-metadata-type' );
		}

		foreach ( $metadata as $key => $value ) {
			if ( !in_array( $key, self::METADATA_KEYS ) ) {
				throw new MalformedBundle(
					'translate-messagebundle-error-invalid-metadata',
					[ $key, Message::listParam( self::METADATA_KEYS ) ]
				);
			}
		}

		$sourceLanguage = $metadata['sourceLanguage'] ?? null;
		if ( $sourceLanguage && !is_string( $sourceLanguage ) ) {
			throw new MalformedBundle(
				'translate-messagebundle-error-invalid-sourcelanguage', [ $sourceLanguage ]
			);
		}

		$priorityLanguageCodes = $metadata['priorityLanguages'] ?? null;
		if ( $priorityLanguageCodes ) {
			if ( !is_array( $priorityLanguageCodes ) ) {
				throw new MalformedBundle( 'translate-messagebundle-error-invalid-prioritylanguage-format' );
			}

			$priorityLanguageCodes = array_unique( $priorityLanguageCodes );
		}

		$description = $metadata['description'] ?? null;
		if ( $description !== null ) {
			if ( !is_string( $description ) ) {
				throw new MalformedBundle(
					'translate-messagebundle-error-invalid-description'
				);
			}

			$description = trim( $description ) === '' ? null : trim( $description );
		}

		$label = $metadata['label'] ?? null;
		if ( $label !== null ) {
			if ( !is_string( $label ) ) {
				throw new MalformedBundle(
					'translate-messagebundle-error-invalid-label'
				);
			}

			$label = trim( $label ) === '' ? null : trim( $label );
		}

		$this->metadata = new MessageBundleMetadata(
			$sourceLanguage,
			$priorityLanguageCodes,
			(bool)( $metadata['allowOnlyPriorityLanguages'] ?? false ),
			$description,
			$label
		);
		return $this->metadata;
	}

	private function getRawData(): array {
		$status = FormatJson::parse( $this->getText(), FormatJson::FORCE_ASSOC );
		if ( !$status->isOK() ) {
			throw new MalformedBundle(
				'translate-messagebundle-error-parsing',
				[ $status->getMessages( 'error' )[0] ]
			);
		}

		$data = $status->getValue();
		// Crude check that we have an associative array (or empty array)
		if ( !is_array( $data ) || ( $data !== [] && array_values( $data ) === $data ) ) {
			throw new MalformedBundle(
				'translate-messagebundle-error-invalid-array',
				[ gettype( $data ) ]
			);
		}

		return $data;
	}
}
