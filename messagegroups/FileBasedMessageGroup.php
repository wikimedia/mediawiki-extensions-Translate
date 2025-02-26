<?php
/**
 * This file a contains a message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\FileFormatSupport\SimpleFormat;
use MediaWiki\Extension\Translate\MessageGroupConfiguration\MetaYamlSchemaExtender;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupCache;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\MessageLoading\MessageDefinitions;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\Utilities;

/**
 * This class implements default behavior for file based message groups.
 *
 * File based message groups are primary type of groups at translatewiki.net,
 * while other projects may use mainly page translation message groups, or
 * custom type of message groups.
 * @ingroup MessageGroup
 */
class FileBasedMessageGroup extends MessageGroupBase implements MetaYamlSchemaExtender {
	public const NO_FILE_FORMAT = 1;

	/** @var array|null */
	protected $reverseCodeMap;

	/**
	 * Constructs a FileBasedMessageGroup from any normal message group.
	 * Useful for doing special Gettext exports from any group.
	 * @param MessageGroup $group
	 * @param string $targetPattern Value for FILES.targetPattern
	 * @return self
	 */
	public static function newFromMessageGroup(
		MessageGroup $group,
		string $targetPattern = ''
	) {
		$conf = [
			'BASIC' => [
				'class' => self::class,
				'id' => $group->getId(),
				'label' => $group->getLabel(),
				'namespace' => $group->getNamespace(),
			],
			'FILES' => [
				'sourcePattern' => '',
				'targetPattern' => $targetPattern,
			],
		];

		$group = MessageGroupBase::factory( $conf );
		if ( !$group instanceof self ) {
			$actual = get_class( $group );
			throw new DomainException( "Expected FileBasedMessageGroup, got $actual" );
		}

		return $group;
	}

	public function getFFS(): SimpleFormat {
		$format = $this->conf['FILES']['format'] ?? null;
		$class = $this->conf['FILES']['class'] ?? null;

		if ( $format !== null ) {
			return Services::getInstance()->getFileFormatFactory()->create( $format, $this );
		} elseif ( $class !== null ) {
			return Services::getInstance()->getFileFormatFactory()->loadInstance( $class, $this );
		} else {
			throw new RuntimeException(
				'FileFormatSupport class/format is not set for "' . $this->getId() . '".',
				self::NO_FILE_FORMAT
			);
		}
	}

	/** @inheritDoc */
	public function exists(): bool {
		return $this->getMessageGroupCache( $this->getSourceLanguage() )->exists();
	}

	/** @inheritDoc */
	public function load( $code ) {
		$ffs = $this->getFFS();
		$data = $ffs->read( $code );

		return $data ? $data['MESSAGES'] : [];
	}

	/**
	 * @param string $code Language tag.
	 * @return array Array with keys MESSAGES, AUTHORS and EXTRA, containing only primitive values.
	 * @since 2020.04
	 */
	public function parseExternal( string $code ): array {
		$supportedKeys = [ 'MESSAGES', 'AUTHORS', 'EXTRA' ];

		$parsedData = $this->getFFS()->read( $code );

		// Ensure we return correct keys
		$data = [];
		foreach ( $supportedKeys as $key ) {
			$data[$key] = $parsedData[$key] ?? [];
		}

		return $data;
	}

	/**
	 * @param string $code Language code.
	 * @return string
	 */
	public function getSourceFilePath( $code ) {
		if ( $this->isSourceLanguage( $code ) ) {
			$pattern = $this->conf['FILES']['definitionFile'] ?? null;
			if ( $pattern !== null ) {
				return $this->replaceVariables( $pattern, $code );
			}
		}

		$pattern = $this->conf['FILES']['sourcePattern'] ?? null;
		if ( $pattern === null ) {
			throw new RuntimeException( 'No source file pattern defined.' );
		}

		return $this->replaceVariables( $pattern, $code );
	}

	public function getTargetFilename( string $code ): string {
		// Check if targetPattern explicitly defined
		$pattern = $this->conf['FILES']['targetPattern'] ?? null;
		if ( $pattern !== null ) {
			return $this->replaceVariables( $pattern, $code );
		}

		// Check if definitionFile is explicitly defined
		if ( $this->isSourceLanguage( $code ) ) {
			$pattern = $this->conf['FILES']['definitionFile'] ?? null;
		}

		// Fallback to sourcePattern which must be defined
		$pattern ??= $this->conf['FILES']['sourcePattern'] ?? null;

		if ( $pattern === null ) {
			throw new RuntimeException( 'No source file pattern defined.' );
		}

		// For exports, the scripts take output directory. We want to
		// return a path where the prefix is current directory instead
		// of full path of the source location.
		$pattern = str_replace( '%GROUPROOT%', '.', $pattern );
		return $this->replaceVariables( $pattern, $code );
	}

	/**
	 * @param string $pattern
	 * @param string $code Language code.
	 * @return string
	 * @since 2014.02 Made public
	 */
	public function replaceVariables( $pattern, $code ) {
		global $IP, $wgTranslateGroupRoot;

		$variables = [
			'%CODE%' => $this->mapCode( $code ),
			'%MWROOT%' => $IP,
			'%GROUPROOT%' => $wgTranslateGroupRoot,
			'%GROUPID%' => $this->getId(),
		];

		return str_replace( array_keys( $variables ), array_values( $variables ), $pattern );
	}

	/**
	 * @param string $code Language code.
	 * @return string
	 */
	public function mapCode( $code ) {
		if ( !isset( $this->conf['FILES']['codeMap'] ) ) {
			return $code;
		}

		if ( isset( $this->conf['FILES']['codeMap'][$code] ) ) {
			return $this->conf['FILES']['codeMap'][$code];
		} else {
			if ( $this->reverseCodeMap === null ) {
				$this->reverseCodeMap = array_flip( $this->conf['FILES']['codeMap'] );
			}

			if ( isset( $this->reverseCodeMap[$code] ) ) {
				return 'x-invalidLanguageCode';
			}

			return $code;
		}
	}

	public static function getExtraSchema(): array {
		$schema = [
			'root' => [
				'_type' => 'array',
				'_children' => [
					'FILES' => [
						'_type' => 'array',
						'_children' => [
							'class' => [
								'_type' => 'text'
							],
							'format' => [
								'_type' => 'text'
							],
							'codeMap' => [
								'_type' => 'array',
								'_ignore_extra_keys' => true,
								'_children' => [],
							],
							'definitionFile' => [
								'_type' => 'text',
							],
							'sourcePattern' => [
								'_type' => 'text',
								'_not_empty' => true,
							],
							'targetPattern' => [
								'_type' => 'text',
							],
						]
					]
				]
			]
		];

		return $schema;
	}

	/** @inheritDoc */
	public function getKeys() {
		$cache = $this->getMessageGroupCache( $this->getSourceLanguage() );
		if ( !$cache->exists() ) {
			return array_keys( $this->getDefinitions() );
		} else {
			return $cache->getKeys();
		}
	}

	/** @inheritDoc */
	public function initCollection( $code ) {
		$namespace = $this->getNamespace();
		$messages = [];

		$cache = $this->getMessageGroupCache( $this->getSourceLanguage() );
		if ( $cache->exists() ) {
			foreach ( $cache->getKeys() as $key ) {
				$messages[$key] = $cache->get( $key );
			}
		}

		$definitions = new MessageDefinitions( $messages, $namespace );
		$collection = MessageCollection::newFromDefinitions( $definitions, $code );
		$this->setTags( $collection );

		return $collection;
	}

	/** @inheritDoc */
	public function getMessage( $key, $code ) {
		$cache = $this->getMessageGroupCache( $code );
		if ( $cache->exists() ) {
			$msg = $cache->get( $key );

			if ( $msg !== false ) {
				return $msg;
			}

			// Try harder
			$nkey = str_replace( ' ', '_', strtolower( $key ) );
			$keys = $cache->getKeys();

			foreach ( $keys as $k ) {
				if ( $nkey === str_replace( ' ', '_', strtolower( $k ) ) ) {
					return $cache->get( $k );
				}
			}

			return null;
		} else {
			return null;
		}
	}

	public function getMessageGroupCache( string $code ): MessageGroupCache {
		$cacheFilePath = Utilities::cacheFile(
			"translate_groupcache-{$this->getId()}/{$code}.cdb"
		);

		return new MessageGroupCache( $this, $code, $cacheFilePath );
	}

	public function getSourceLanguage(): string {
		// This is set in FileBasedMessageGroupFactory, so the fallback is only used if
		// something is creating these groups manually.
		return $this->conf['BASIC']['sourcelanguage'] ?? 'en';
	}
}
