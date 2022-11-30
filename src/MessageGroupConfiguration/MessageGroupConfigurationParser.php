<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use AggregateMessageGroup;
use Exception;
use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;
use RomaricDrigon\MetaYaml\MetaYaml;
use TranslateYaml;

/**
 * Utility class to parse and validate message group configurations.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class MessageGroupConfigurationParser {
	private $baseSchema;

	public function __construct() {
		// Don't perform validations if library not available
		if ( class_exists( MetaYaml::class ) ) {
			$this->baseSchema = $this->getBaseSchema();
		}
	}

	/**
	 * Easy to use function to get valid group configurations from YAML. Those not matching
	 * schema will be ignored, if schema validation is enabled.
	 *
	 * @param string $data Yaml
	 * @param callable|null $callback Optional callback which is called on errors. Parameters are
	 * document index, processed configuration and error message.
	 * @return array Group configurations indexed by message group id.
	 */
	public function getHopefullyValidConfigurations( string $data, ?callable $callback = null ): array {
		if ( !is_callable( $callback ) ) {
			$callback = static function ( $unused1, $unused2, $unused3 ) {
				/*noop*/
			};
		}

		$documents = self::getDocumentsFromYaml( $data );
		$configurations = self::parseDocuments( $documents );
		$groups = [];

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
	 * @return string[]
	 */
	public function getDocumentsFromYaml( string $data ): array {
		return preg_split( "/^---$/m", $data, -1, PREG_SPLIT_NO_EMPTY );
	}

	/**
	 * Returns group configurations from YAML documents. If there is document containing template,
	 * it will be merged with other configurations.
	 *
	 * @return array[][] Unvalidated group configurations
	 */
	public function parseDocuments( array $documents ): array {
		$groups = [];
		$template = [];

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
				if ( $groups[$i]['BASIC']['class'] === AggregateMessageGroup::class ) {
					unset( $groups[$i]['FILES'] );
				}
			}
		}

		return $groups;
	}

	public function getBaseSchema(): array {
		return TranslateYaml::load( __DIR__ . '/../../data/group-yaml-schema.yaml' );
	}

	/**
	 * Validates group configuration against schema.
	 * @throws Exception If configuration is not valid.
	 */
	public function validate( array $config ): void {
		$schema = $this->baseSchema;

		foreach ( $config as $section ) {
			if ( !isset( $section['class'] ) ) {
				continue;
			}

			$class = $section['class'];

			// FIXME: UGLY HACK: StringMatcher is now under a namespace so use the fully prefixed
			// class to check if it has the getExtraSchema method
			if ( $class === 'StringMatcher' ) {
				$class = StringMatcher::class;
			}

			// There is no sane way to check whether *class* implements interface in PHP
			if ( !is_callable( [ $class, 'getExtraSchema' ] ) ) {
				continue;
			}

			$extra = call_user_func( [ $class, 'getExtraSchema' ] );
			$schema = array_replace_recursive( $schema, $extra );
		}

		$schema = new MetaYaml( $schema );
		$schema->validate( $config );
	}

	/** Merges a document template (base) to actual definition (specific) */
	public static function mergeTemplate( array $base, array $specific ): array {
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
