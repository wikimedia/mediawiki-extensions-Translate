<?php
/**
 * This file a contains a message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * This class implements default behavior for file based message groups.
 *
 * File based message groups are primary type of groups at translatewiki.net,
 * while other projects may use mainly page translation message groups, or
 * custom type of message groups.
 * @ingroup MessageGroup
 */
class FileBasedMessageGroup extends MessageGroupBase implements MetaYamlSchemaExtender {
	protected $reverseCodeMap;

	/**
	 * Constructs a FileBasedMessageGroup from any normal message group.
	 * Useful for doing special Gettext exports from any group.
	 * @param MessageGroup $group
	 * @return FileBasedMessageGroup
	 */
	public static function newFromMessageGroup( $group ) {
		$conf = [
			'BASIC' => [
				'class' => 'FileBasedMessageGroup',
				'id' => $group->getId(),
				'label' => $group->getLabel(),
				'namespace' => $group->getNamespace(),
			],
			'FILES' => [
				'sourcePattern' => '',
				'targetPattern' => '',
			],
		];

		return MessageGroupBase::factory( $conf );
	}

	public function exists() {
		return $this->getFFS()->exists();
	}

	public function load( $code ) {
		/** @var $ffs FFS */
		$ffs = $this->getFFS();
		$data = $ffs->read( $code );

		return $data ? $data['MESSAGES'] : [];
	}

	/**
	 * @param string $code Language code.
	 * @return string
	 * @throws MWException
	 */
	public function getSourceFilePath( $code ) {
		if ( $this->isSourceLanguage( $code ) ) {
			$pattern = $this->getFromConf( 'FILES', 'definitionFile' );
			if ( $pattern !== null ) {
				return $this->replaceVariables( $pattern, $code );
			}
		}

		$pattern = $this->getFromConf( 'FILES', 'sourcePattern' );
		if ( $pattern === null ) {
			throw new MWException( 'No source file pattern defined.' );
		}

		return $this->replaceVariables( $pattern, $code );
	}

	public function getTargetFilename( $code ) {
		// Check if targetPattern explicitly defined
		$pattern = $this->getFromConf( 'FILES', 'targetPattern' );
		if ( $pattern !== null ) {
			return $this->replaceVariables( $pattern, $code );
		}

		// Check if definitionFile is explicitly defined
		if ( $this->isSourceLanguage( $code ) ) {
			$pattern = $this->getFromConf( 'FILES', 'definitionFile' );
		}

		// Fallback to sourcePattern which must be defined
		if ( $pattern === null ) {
			$pattern = $this->getFromConf( 'FILES', 'sourcePattern' );
		}

		if ( $pattern === null ) {
			throw new MWException( 'No source file pattern defined.' );
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
		];

		Hooks::run( 'TranslateMessageGroupPathVariables', [ $this, &$variables ] );

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
			if ( !isset( $this->reverseCodeMap ) ) {
				$this->reverseCodeMap = array_flip( $this->conf['FILES']['codeMap'] );
			}

			if ( isset( $this->reverseCodeMap[$code] ) ) {
				return 'x-invalidLanguageCode';
			}

			return $code;
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
							'class' => [
								'_type' => 'text',
								'_not_empty' => true,
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
}
