<?php
/**
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Utility class to parse and validate message group configurations.
 * @since 2014.01
 */
class MessageGroupConfigurationParser {
	protected $baseSchema;

	public function __construct() {
		// Don't perform validations if library not available
		if ( class_exists( 'RomaricDrigon\MetaYaml\MetaYaml' ) ) {
			$this->baseSchema = $this->getBaseSchema();
		}
	}

	/**
	 * Easy to use function to get valid group configurations from YAML. Those not matching
	 * schema will be ignored, if schema validation is enabled.
	 *
	 * @param string $data Yaml
	 * @param callable $callback Optional callback which is called on errors. Parameters are
	 *   document index, processed configuration and error message.
	 * @return array Group configurations indexed by message group id.
	 */
	public function getHopefullyValidConfigurations( $data, $callback = null ) {
		if ( !is_callable( $callback ) ) {
			$callback = function () {
				/*noop*/
			};
		}

		$documents = self::getDocumentsFromYaml( $data );
		$configurations = self::parseDocuments( $documents );
		$groups = array();

		if ( is_array( $this->baseSchema ) ) {
			foreach ( $configurations as $index => $config ) {
				try {
					$this->validate( $config );
					$groups[$config['BASIC']['id']] = $config;
				} catch ( Exception $e ) {
					$callback( $index, $config, $e->getMessage() );
				}
			}
		} else {
			foreach ( $configurations as $index => $config ) {
				if ( isset( $config['BASIC']['id'] ) ) {
					$groups[$config['BASIC']['id']] = $config;
				} else {
					$callback( $index, $config, 'id is missing' );
				}
			}
		}

		return $groups;
	}

	/**
	 * Given a Yaml string, returns the non-empty documents as an array.
	 *
	 * @param string $data
	 * @return string[]
	 */
	public function getDocumentsFromYaml( $data ) {
		return preg_split( "/^---$/m", $data, -1, PREG_SPLIT_NO_EMPTY );
	}

	/**
	 * Returns group configurations from YAML documents. If there is document containing template,
	 * it will be merged with other configurations.
	 *
	 * @param array $documents
	 * @return array Unvalidated group configurations
	 */
	public function parseDocuments( array $documents ) {
		$groups = array();
		$template = array();

		foreach ( $documents as $document ) {
			$document = TranslateYaml::loadString( $document );

			if ( isset( $document['TEMPLATE'] ) ) {
				$template = $document['TEMPLATE'];
			} else {
				$groups[] = $document;
			}
		}

		if ( $template ) {
			foreach ( $groups as $i => $group ) {
				$groups[$i] = self::mergeTemplate( $template, $group );
				// Little hack to allow aggregate groups to be defined in same file with other groups.
				if ( $groups[$i]['BASIC']['class'] === 'AggregateMessageGroup' ) {
					unset( $groups[$i]['FILES'] );
				}
			}
		}

		return $groups;
	}

	public function getBaseSchema() {
		return TranslateYaml::load( __DIR__ . '/data/group-yaml-schema.yaml' );
	}

	/**
	 * Validates group configuration against schema.
	 *
	 * @param array $config
	 * @throws Exception If configuration is not valid.
	 */
	public function validate( array $config ) {
		$schema = $this->baseSchema;

		foreach ( $config as $sectionName => $section ) {
			if ( !isset( $section['class'] ) ) {
				continue;
			}

			$class = $section['class'];
			// There is no sane way to check whether *class* implements interface in PHP
			if ( !method_exists( $class, 'getExtraSchema' ) ) {
				continue;
			}

			$extra = call_user_func( array( $class, 'getExtraSchema' ) );
			$schema = array_replace_recursive( $schema, $extra );
		}

		$schema = new RomaricDrigon\MetaYaml\MetaYaml( $schema );
		$schema->validate( $config );
	}

	/**
	 * Merges a document template (base) to actual definition (specific)
	 * @param array $base
	 * @param array $specific
	 * @return array
	 */
	public static function mergeTemplate( array $base, array $specific ) {
		foreach ( $specific as $key => $value ) {
			if ( is_array( $value ) && isset( $base[$key] ) && is_array( $base[$key] ) ) {
				$base[$key] = self::mergeTemplate( $base[$key], $value );
			} else {
				$base[$key] = $value;
			}
		}

		return $base;
	}
}
