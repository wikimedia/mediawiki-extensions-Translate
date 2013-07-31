<?php
/**
 * This file a contains a message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * This class implements default behavior for file based message groups.
 *
 * File based message groups are primary type of groups at translatewiki.net,
 * while other projects may use mainly page translation message groups, or
 * custom type of message groups.
 * @ingroup MessageGroup
 */
class FileBasedMessageGroup extends MessageGroupBase {
	protected $reverseCodeMap;

	/**
	 * Constructs a FileBasedMessageGroup from any normal message group.
	 * Useful for doing special Gettext exports from any group.
	 * @param $group MessageGroup
	 * @return FileBasedMessageGroup
	 */
	public static function newFromMessageGroup( $group ) {
		$conf = array(
			'BASIC' => array(
				'class' => 'FileBasedMessageGroup',
				'id' => $group->getId(),
				'label' => $group->getLabel(),
				'namespace' => $group->getNamespace(),
			),
			'FILES' => array(
				'sourcePattern' => '',
				'targetPattern' => '',
			),
		);

		return MessageGroupBase::factory( $conf );
	}

	public function exists() {
		return $this->getFFS()->exists();
	}

	public function load( $code ) {
		/**
		 * @var $ffs FFS
		 */
		$ffs = $this->getFFS();
		$data = $ffs->read( $code );

		return $data ? $data['MESSAGES'] : array();
	}

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
		$pattern = $this->getFromConf( 'FILES', 'targetPattern' );

		if ( $pattern === null ) {
			throw new MWException( 'No target file pattern defined.' );
		}

		return $this->replaceVariables( $pattern, $code );
	}

	protected function replaceVariables( $pattern, $code ) {
		global $IP, $wgTranslateGroupRoot;

		$variables = array(
			'%CODE%' => $this->mapCode( $code ),
			'%MWROOT%' => $IP,
			'%GROUPROOT%' => $wgTranslateGroupRoot,
		);

		return str_replace( array_keys( $variables ), array_values( $variables ), $pattern );
	}

	/**
	 * @param $code
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
}
