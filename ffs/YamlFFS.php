<?php

/**
 * Implements support for message storage in YAML format.
 *
 * This class adds new key into FILES section: \c codeAsRoot.
 * If it is set to true, all messages will under language code.
 * @ingroup FFS
 */
class YamlFFS extends SimpleFFS implements MetaYamlSchemaExtender {

	/**
   * @param $group FileBasedMessageGroup
   */
  public function __construct( FileBasedMessageGroup $group ) {
	  parent::__construct( $group );
		$this->flattener = $this->getFlattener();
	}

	public function getFileExtensions() {
		return array( '.yaml', '.yml' );
	}

	/**
	 * @param $data
	 * @return array Parsed data.
	 */
	public function readFromVariable( $data ) {
		// Authors first.
		$matches = array();
		preg_match_all( '/^#\s*Author:\s*(.*)$/m', $data, $matches );
		$authors = $matches[1];

		// Then messages.
		$messages = TranslateYaml::loadString( $data );

		// Some groups have messages under language code
		if ( isset( $this->extra['codeAsRoot'] ) ) {
			$messages = array_shift( $messages );
		}

		$messages = $this->flatten( $messages );
		$messages = $this->group->getMangler()->mangle( $messages );
		foreach ( $messages as &$value ) {
			$value = rtrim( $value, "\n" );
		}

		return array(
			'AUTHORS' => $authors,
			'MESSAGES' => $messages,
		);
	}

	/**
	 * @param $collection MessageCollection
	 * @return string
	 */
	protected function writeReal( MessageCollection $collection ) {
		$output = $this->doHeader( $collection );
		$output .= $this->doAuthors( $collection );

		$mangler = $this->group->getMangler();

		$messages = array();
		/**
		 * @var $m TMessage
		 */
		foreach ( $collection as $key => $m ) {
			$key = $mangler->unmangle( $key );
			$value = $m->translation();
			$value = str_replace( TRANSLATE_FUZZY, '', $value );

			if ( $value === '' ) {
				continue;
			}

			$messages[$key] = $value;
		}

		if ( !count( $messages ) ) {
			return false;
		}

		$messages = $this->unflatten( $messages );

		// Some groups have messages under language code.
		if ( isset( $this->extra['codeAsRoot'] ) ) {
			$code = $this->group->mapCode( $collection->code );
			$messages = array( $code => $messages );
		}

		$output .= TranslateYaml::dump( $messages );

		return $output;
	}

	/**
	 * @param $collection MessageCollection
	 * @return string
	 */
	protected function doHeader( MessageCollection $collection ) {
		global $wgSitename;
		global $wgTranslateYamlLibrary;

		$code = $collection->code;
		$name = TranslateUtils::getLanguageName( $code );
		$native = TranslateUtils::getLanguageName( $code, $code );
		$output = "# Messages for $name ($native)\n";
		$output .= "# Exported from $wgSitename\n";

		if ( isset( $wgTranslateYamlLibrary ) ) {
			$output .= "# Export driver: $wgTranslateYamlLibrary\n";
		}

		return $output;
	}

	/**
	 * @param $collection MessageCollection
	 * @return string
	 */
	protected function doAuthors( MessageCollection $collection ) {
		$output = '';
		$authors = $collection->getAuthors();
		$authors = $this->filterAuthors( $authors, $collection->code );

		foreach ( $authors as $author ) {
			$output .= "# Author: $author\n";
		}

		return $output;
	}

	/**
	 * Obtains object used to flatten and unflatten arrays. In this implementation
	 * we use the ArrayFlattener class which also supports CLDR pluralization rules.
	 *
	 * @return object with flatten, unflatten methods
	 */
	protected function getFlattener() {
		$nestingSeparator = isset( $this->extra['nestingSeparator'] ) ?
			$this->extra['nestingSeparator'] : '.';
		$parseCLDRPlurals = isset( $this->extra['parseCLDRPlurals'] ) ?
			$this->extra['parseCLDRPlurals'] : false;

		// Instantiate helper class for flattening and unflattening nested arrays
		return new ArrayFlattener( $nestingSeparator, $parseCLDRPlurals );
	}

	/**
	 * Flattens multidimensional array by using the path to the value as key
	 * with each individual key separated by a dot.
	 *
	 * @param $messages array
	 *
	 * @return array
	 */
	protected function flatten( $messages ) {
		return $this->flattener->flatten( $messages );
	}


	/**
	 * Performs the reverse operation of flatten. Each dot (or custom separator)
	 * in the key starts a new subarray in the final array.
	 *
	 * @param $messages array
	 *
	 * @return array
	 */
	protected function unflatten( $messages ) {
		return $this->flattener->unflatten( $messages );
	}

	public static function getExtraSchema() {
		$schema = array(
			'root' => array(
				'_type' => 'array',
				'_children' => array(
					'FILES' => array(
						'_type' => 'array',
						'_children' => array(
							'codeAsRoot' => array(
								'_type' => 'boolean',
							),
							'nestingSeparator' => array(
								'_type' => 'text',
							),
							'parseCLDRPlurals' => array(
								'_type' => 'boolean',
							)
						)
					)
				)
			)
		);

		return $schema;
	}
}
