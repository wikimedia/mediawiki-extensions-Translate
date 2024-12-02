<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWiki\Extension\Translate\MessageLoading\Message;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\MessageProcessing\ArrayFlattener;
use MediaWiki\Json\FormatJson;

/**
 * JsonFormat implements a message format where messages are encoded
 * as key-value pairs in JSON objects. The format is extended to
 * support author information under the special @metadata key.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup FileFormatSupport
 */
class JsonFormat extends SimpleFormat {
	private ?ArrayFlattener $flattener;

	public static function isValid( string $data ): bool {
		return is_array( FormatJson::decode( $data, /*as array*/true ) );
	}

	public function __construct( FileBasedMessageGroup $group ) {
		parent::__construct( $group );
		$this->flattener = $this->getFlattener();
	}

	public function getFileExtensions(): array {
		return [ '.json' ];
	}

	/** @return array Parsed data. */
	public function readFromVariable( string $data ): array {
		$messages = (array)FormatJson::decode( $data, /*as array*/true );
		$authors = [];
		$metadata = [];

		if ( isset( $messages['@metadata']['authors'] ) ) {
			$authors = (array)$messages['@metadata']['authors'];
			unset( $messages['@metadata']['authors'] );
		}

		if ( isset( $messages['@metadata'] ) ) {
			$metadata = $messages['@metadata'];
		}

		unset( $messages['@metadata'] );

		if ( $this->flattener ) {
			$messages = $this->flattener->flatten( $messages );
		}

		$messages = $this->group->getMangler()->mangleArray( $messages );

		return [
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
			'EXTRA' => [ 'METADATA' => $metadata ],
		];
	}

	protected function writeReal( MessageCollection $collection ): string {
		$template = $this->read( $collection->getLanguage() ) ?: [];
		$authors = $this->filterAuthors( $collection->getAuthors(), $collection->getLanguage() );
		$messages = [];

		/** @var Message $m */
		foreach ( $collection as $key => $m ) {
			$value = $m->translation();
			if ( $value === null ) {
				continue;
			}

			if ( $m->hasTag( 'fuzzy' ) ) {
				$value = str_replace( TRANSLATE_FUZZY, '', $value );
			}

			$messages[$key] = $value;
		}

		// Do not create files without translations
		if ( $messages === [] ) {
			return '';
		}

		$template['MESSAGES'] = $messages;
		$template['AUTHORS'] = $authors;

		return $this->generateFile( $template );
	}

	public function generateFile( array $template ): string {
		$messages = $template['MESSAGES'];
		$authors = $template['AUTHORS'];

		if ( $this->flattener ) {
			$messages = $this->flattener->unflatten( $messages );
		}

		$mangler = $this->group->getMangler();
		$messages = $mangler->unmangleArray( $messages );

		if ( $this->extra['includeMetadata'] ?? true ) {
			$metadata = $template['EXTRA']['METADATA'] ?? [];
			$metadata['authors'] = $authors;

			$messages = [ '@metadata' => $metadata ] + $messages;
		}

		return FormatJson::encode( $messages, "\t", FormatJson::ALL_OK ) . "\n";
	}

	private function getFlattener(): ?ArrayFlattener {
		if ( !isset( $this->extra['nestingSeparator'] ) ) {
			return null;
		}

		$parseCLDRPlurals = $this->extra['parseCLDRPlurals'] ?? false;

		return new ArrayFlattener( $this->extra['nestingSeparator'], $parseCLDRPlurals );
	}

	public function isContentEqual( ?string $a, ?string $b ): bool {
		if ( $this->flattener ) {
			return $this->flattener->compareContent( $a, $b );
		} else {
			return parent::isContentEqual( $a, $b );
		}
	}

	public static function getExtraSchema(): array {
		return [
			'root' => [
				'_type' => 'array',
				'_children' => [
					'FILES' => [
						'_type' => 'array',
						'_children' => [
							'nestingSeparator' => [
								'_type' => 'text',
							],
							'parseCLDRPlurals' => [
								'_type' => 'boolean',
							],
							'includeMetadata' => [
								'_type' => 'boolean',
							]
						]
					]
				]
			]
		];
	}
}

class_alias( JsonFormat::class, 'JsonFFS' );
