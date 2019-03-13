<?php
/**
 * Support for JSON message file format.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * JsonFFS implements a message format where messages are encoded
 * as key-value pairs in JSON objects. The format is extended to
 * support author information under the special @metadata key.
 *
 * @ingroup FFS
 * @since 2012-09-21
 */
class JsonFFS extends SimpleFFS {
	/**
	 * @param string $data
	 * @return bool
	 */
	public static function isValid( $data ) {
		return is_array( FormatJson::decode( $data, /*as array*/true ) );
	}

	/**
	 * @param FileBasedMessageGroup $group
	 */
	public function __construct( FileBasedMessageGroup $group ) {
		parent::__construct( $group );
		$this->flattener = $this->getFlattener();
	}

	public function getFileExtensions() {
		return [ '.json' ];
	}

	/**
	 * @param string $data
	 * @return array Parsed data.
	 */
	public function readFromVariable( $data ) {
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

		$messages = $this->group->getMangler()->mangle( $messages );

		return [
			'MESSAGES' => $messages,
			'AUTHORS' => $authors,
			'METADATA' => $metadata,
		];
	}

	/**
	 * @param MessageCollection $collection
	 * @return string
	 */
	protected function writeReal( MessageCollection $collection ) {
		$messages = [];
		$template = $this->read( $collection->getLanguage() );

		$messages['@metadata'] = [];
		if ( isset( $template['METADATA'] ) ) {
			$messages['@metadata'] = $template['METADATA'];
		}

		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		if ( isset( $template['AUTHORS'] ) ) {
			$authors = array_unique( array_merge( $template['AUTHORS'], $authors ) );
		}

		if ( $authors !== [] ) {
			$messages['@metadata']['authors'] = array_values( $authors );
		}

		$mangler = $this->group->getMangler();

		/**
		 * @var $m ThinMessage
		 */
		foreach ( $collection as $key => $m ) {
			$value = $m->translation();
			if ( $value === null ) {
				continue;
			}

			if ( $m->hasTag( 'fuzzy' ) ) {
				$value = str_replace( TRANSLATE_FUZZY, '', $value );
			}

			$key = $mangler->unmangle( $key );
			$messages[$key] = $value;
		}

		// Do not create empty files. Check that something besides @metadata is present.
		if ( count( $messages ) < 2 ) {
			return '';
		}

		if ( $this->flattener ) {
			$messages = $this->flattener->unflatten( $messages );
		}

		if ( isset( $this->extra['includeMetadata'] ) && !$this->extra['includeMetadata'] ) {
			unset( $messages['@metadata'] );
		}

		return FormatJson::encode( $messages, "\t", FormatJson::ALL_OK ) . "\n";
	}

	protected function getFlattener() {
		if ( !isset( $this->extra['nestingSeparator'] ) ) {
			return null;
		}

		$parseCLDRPlurals = $this->extra['parseCLDRPlurals'] ?? false;
		$flattener = new ArrayFlattener( $this->extra['nestingSeparator'], $parseCLDRPlurals );

		return $flattener;
	}

	public function isContentEqual( $a, $b ) {
		if ( $this->flattener ) {
			return $this->flattener->compareContent( $a, $b );
		} else {
			return parent::isContentEqual( $a, $b );
		}
	}

	public static function getExtraSchema() {
		$schema = [
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

		return $schema;
	}
}
