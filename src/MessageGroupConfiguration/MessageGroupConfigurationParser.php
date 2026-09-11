<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupConfiguration;

use AggregateMessageGroup;
use Exception;
use MediaWiki\Extension\Translate\FileFormatSupport\FileFormatFactory;
use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\Yaml;
use RomaricDrigon\MetaYaml\MetaYaml;

/**
 * Utility class to parse and validate message group configurations.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
class MessageGroupConfigurationParser {
	private ?array $baseSchema = null;
	private FileFormatFactory $fileFormatFactory;

	public function __construct() {
		// Don't perform validations if library not available
		if ( class_exists( MetaYaml::class ) ) {
			$this->baseSchema = $this->getBaseSchema();
		}

		$this->fileFormatFactory = Services::getInstance()->getFileFormatFactory();
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
				try {
					Services::getInstance()->getMessageGroupFactory()->resolveClass( $config );
					if ( isset( $config['BASIC']['id'] ) ) {
						$groups[$config['BASIC']['id']] = $config;
					} else {
						$callback( $index, $config, 'id is missing' );
					}
				} catch ( InvalidGroupConfigurationException $e ) {
					$callback( $index, $config, $e->getMessage() );
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
			$document = Yaml::loadString( $document );

			if ( isset( $document['TEMPLATE'] ) ) {
				$template = $document['TEMPLATE'];
			} else {
				$groups[] = $document;
			}
		}

		if ( $template ) {
			foreach ( $groups as $i => $group ) {
				$merged = self::mergeTemplate( $template, $group );
				$merged = $this->resolveTemplateSelector( $group, $merged );
				// Little hack to allow aggregate groups to be defined in same file with other groups.
				if ( $this->isAggregateGroup( $merged ) ) {
					unset( $merged['FILES'] );
				}
				$groups[$i] = $merged;
			}
		}

		return $groups;
	}

	/**
	 * After template merging, ensure the effective configuration contains exactly
	 * one implementation selector (type or class).
	 *
	 * Rules:
	 *  1. Concrete specifies type → remove inherited class.
	 *  2. Concrete specifies class → remove inherited type.
	 *  3. Concrete specifies neither → inherit normally (already done by mergeTemplate).
	 *  4. Concrete specifies both → left for validate() to reject.
	 */
	private function resolveTemplateSelector( array $specific, array $merged ): array {
		$concreteBasic = $specific['BASIC'] ?? [];
		$hasConcreteType = isset( $concreteBasic['type'] );
		$hasConcreteClass = isset( $concreteBasic['class'] );

		if ( $hasConcreteType && !$hasConcreteClass ) {
			unset( $merged['BASIC']['class'] );
		} elseif ( $hasConcreteClass && !$hasConcreteType ) {
			unset( $merged['BASIC']['type'] );
		}

		return $merged;
	}

	/**
	 * Return whether the effective configuration represents an aggregate group.
	 */
	private function isAggregateGroup( array $config ): bool {
		try {
			$class = Services::getInstance()->getMessageGroupFactory()->resolveClass( $config );
			return is_a( $class, AggregateMessageGroup::class, allow_string: true );
		} catch ( InvalidGroupConfigurationException ) {
			return false;
		}
	}

	public function getBaseSchema(): array {
		return Yaml::load( __DIR__ . '/../../data/group-yaml-schema.yaml' );
	}

	/**
	 * Validates group configuration against schema.
	 * @throws InvalidGroupConfigurationException If the selector is invalid.
	 * @throws Exception If configuration does not match the schema.
	 */
	public function validate( array $config ): void {
		// Validate the selector resolves (catches unknown type IDs, missing/duplicate selectors).
		$implClass = Services::getInstance()->getMessageGroupFactory()->resolveClass( $config );

		$schema = $this->baseSchema;

		foreach ( $config as $key => $section ) {
			$extra = [];
			if ( $key === 'FILES' ) {
				$extra = $this->getFilesSchemaExtra( $section );
			} elseif ( $key === 'MANGLER' ) {
				$class = $section[ 'class' ] ?? null;
				// FIXME: UGLY HACK: StringMatcher is now under a namespace so use the fully prefixed
				// class to check if it has the getExtraSchema method
				if ( $class === 'StringMatcher' ) {
					$extra = StringMatcher::getExtraSchema();
				}
			} elseif ( $key === 'BASIC' ) {
				$extra = $this->callGetExtraSchema( $implClass );
			} else {
				$extra = $this->callGetExtraSchema( $section[ 'class' ] ?? null );
			}

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

	private function getFilesSchemaExtra( array $section ): array {
		$class = $section['class'] ?? null;
		$format = $section['format'] ?? null;
		$className = null;

		if ( $format ) {
			$className = $this->fileFormatFactory->getClassname( $format );
		} elseif ( $class ) {
			$className = $class;
		}

		return $this->callGetExtraSchema( $className );
	}

	private function callGetExtraSchema( ?string $className ): array {
		if ( $className && is_callable( [ $className, 'getExtraSchema' ] ) ) {
			return $className::getExtraSchema();
		}

		return [];
	}
}
