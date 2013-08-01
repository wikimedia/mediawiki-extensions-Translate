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
 * New style message group for %MediaWiki.
 * @todo Currently unused
 * @ingroup MessageGroup
 */
class MediaWikiMessageGroup extends FileBasedMessageGroup {
	public function mapCode( $code ) {
		return ucfirst( str_replace( '-', '_', parent::mapCode( $code ) ) );
	}

	public function getTags( $type = null ) {
		$path = $this->getFromConf( 'BASIC', 'metadataPath' );

		if ( $path === null ) {
			throw new MWException( "metadataPath is not configured." );
		}

		$filename = "$path/messageTypes.inc";

		if ( !is_readable( $filename ) ) {
			throw new MWException( "$filename is not readable." );
		}

		$data = file_get_contents( $filename );

		if ( $data === false ) {
			throw new MWException( "Failed to read $filename." );
		}

		$reader = new ConfEditor( $data );
		$vars = $reader->getVars();

		$tags = array();
		$tags['optional'] = $vars['wgOptionalMessages'];
		$tags['ignored'] = $vars['wgIgnoredMessages'];

		if ( !$type ) {
			return $tags;
		}

		if ( isset( $tags[$type] ) ) {
			return $tags[$type];
		}

		return array();
	}
}
