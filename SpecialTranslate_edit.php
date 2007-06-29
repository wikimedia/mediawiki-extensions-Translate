<?php

class SpecialTranslateEditTools {
	static function addTools( $object ) {
		if( $object->mTitle->getNamespace() == NS_MEDIAWIKI ) {
			$object->editFormTextTop .= self::editBoxes( $object );
			$object->editFormTextTop .= self::messageFormat( $object );
		}
		return true;
	}

	private static function messageFormat( $object ) {
		/* Provide additional details */
		list( $key, ) = self::figureMessage( $object );
		$zxx = Language::factory( 'zxx' );
		$info = $zxx->getMessage( $key );
		$info = STools::indexOf( explode( ';', $info, 2), 0);
		if ( $info === null ||
			!in_array( $info, array('parsed', 'plain', 'magic', 'unescaped') ) ) {
			$info = 'unknown';
		}
		return wfMsgExt( 'translate-edit-message-format', array( 'parse' ), $info );
 }

	private static function doBox( $msg, $code, $i18nmsg ) {
		static $names = false;
		if (!$names ) { $names = Language::getLanguageNames(); }
		if (!$msg ) { return ''; }

		$prettyCode = STools::prettyCode( $code );

		/* Approximate row count */
		$rows = count(explode("\n", $msg)) -1;
		$rows = max(3, min(15, $rows));

		return
			wfMsg( $i18nmsg, $names[$code], $prettyCode ) . " " .
			Xml::Element( 'textarea', array( 'rows' => $rows ), $msg );
	}

	/**
	* @return Array of the message and the language
	*/
	private static function figureMessage( $object ) {
		global $wgContLanguageCode, $wgContLang;
		$pieces = explode('/', $wgContLang->lcfirst($object->mTitle->getDBkey()), 3);

		$key = $pieces[0];

		# Language the user is translating to
		$langCode = isset($pieces[1]) ? $pieces[1] : $wgContLanguageCode;
		return array( $key, $langCode );
	}

	private static function editBoxes( $object ) {
		list( $key, $langCode ) = self::figureMessage( $object );

		require( 'SpecialTranslate_exts.php' );
		$classes = efInitializeExtensionClasses( );

		$msgArray = Language::getMessagesFor( 'en' );
		foreach ( $classes as $class ) {
			$msgArray = array_merge( $msgArray, $class->getArray() );
		}

		if ( !isset( $msgArray[$key] ) ) { return ''; }

		$boxes = array();
		$boxes[] = self::dobox( $msgArray[$key], 'en', 'translate-edit-message-in' );


		$langFBcode = Language::getFallbackFor( $langCode );
		if ( $langFBcode && $langFBcode !== 'en' ) {
			$messages = self::mergedMessages( $classes, $langFBcode );
			if ( isset( $messages[$key] ) ) {
				$boxes[] = self::dobox( $messages[$key], $langFBcode, 'translate-edit-message-in-fb' );
			}
		}

		if ( $langCode !== 'en' ) {
			$messages = self::mergedMessages( $classes, $langCode );
			if ( isset( $messages[$key] ) ) {
				$boxes[] = self::dobox( $messages[$key], $langCode, 'translate-edit-message-in' );
			}
		}

		return implode("\n\n", $boxes);
	}

	private static function mergedMessages( $classes, $code ) {
		$messages = STools::getMessagesInFile( $code );
		if (!$messages ) { $messages = array(); }
		foreach ( $classes as $class ) {
			if ($class->getId() === 'core') { continue; }
			$messages = array_merge( $messages, $class->getArray( $code ) );
		}
		return $messages;
	}

}

