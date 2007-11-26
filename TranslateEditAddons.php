<?php

class TranslateEditAddons {
	static function addTools( $object ) {
		if( $object->mTitle->getNamespace() == NS_MEDIAWIKI ) {
			$object->editFormTextTop .= self::editBoxes( $object );
			//$object->editFormTextTop .= self::messageFormat( $object );
		}
		return true;
	}

	private static function getFallbacks( $code ) {
		global $wgTranslateLanguageFallbacks;

		$fallbacks = array();
		if ( isset($wgTranslateLanguageFallbacks[$code]) ) {
				$temp = $wgTranslateLanguageFallbacks[$code];
			if (!is_array($temp) ) {
				$fallbacks = array( $temp );
			} else {
				$fallbacks = $temp;
			}
		}

		$realFallback = Language::getFallbackFor( $code );
		if ( $realFallback && $realFallback !== 'en' ) {
			$fallbacks = array_merge( array($realFallback), $fallbacks );
		}

		return $fallbacks;
	}

	private static function doBox( $msg, $code, $i18nmsg ) {
		global $wgUser, $wgLang;
		if (!$msg ) { return ''; }

		$name = TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() );
		$code = strtolower( $code );

		/* Approximate row count */
		$cols = $wgUser->getOption( 'cols' );

		$rows = 0;
		foreach ( explode("\n", $msg) as $l ) {
			$rows += ceil( mb_strlen( $l ) / $cols );
		}
		$rows = max(3, min(15, $rows));

		wfLoadExtensionMessages( 'Translate' );

		return
			wfMsg( $i18nmsg, $name, $code ) . " " .
			Xml::element( 'textarea', array( 'rows' => $rows, 'cols' => $cols ), $msg );
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

	/**
	 * Tries to determine from which group this message belongs. It tries to get
	 * group id from loadgroup GET-paramater, but fallbacks to messageIndex file
	 * if no valid group was provided, or the group provided is a meta group.
	 * @param $key The message key we are interested in.
	 * @return MessageGroup which the key belongs to, or null.
	 */
	private static function getMessageGroup( $key ) {
		global $wgRequest;
		$group = $wgRequest->getText('loadgroup', '' );
		$mg = MessageGroups::getGroup( $group );

		# If we were not given group, or the group given was meta...
		if ( is_null( $mg ) || $mg->isMeta() ) {
			# .. then try harder, because meta groups are *inefficient*
			$group = TranslateUtils::messageKeyToGroup( $key );
			if ( $group ) {
				$mg = MessageGroups::getGroup( $group );
			}
		}

		return $mg;
	}

	private static function editBoxes( $object ) {
		list( $key, $code ) = self::figureMessage( $object );

		$group = self::getMessageGroup( $key );
		if ( $group === null ) return;

		$en = $group->getMessage( $key, 'en' );
		$xx = $group->getMessage( $key, $code );


		$boxes = array();
		if ( $en !== null ) {
			$boxes[] = self::dobox( $en, 'en', 'translate-edit-message-in' );
		}

		foreach ( self::getFallbacks( $code ) as $fbcode ) {
			$fb = $group->getMessage( $key, $fbcode );
			/* For fallback, even uncommitted translation may be useful */
			if ( $fb === null ) {
				$fb = TranslateUtils::getMessageContent( $key, $fbcode );
			}
			if ( $fb !== null ) {
				$boxes[] = self::dobox( $fb, $fbcode, 'translate-edit-message-in-fb' );
			}
		}

		if ( $xx !== null && $code !== 'en' ) {
			$boxes[] = self::dobox( $xx, $code, 'translate-edit-message-in' );
		
		// Hack initial content
			if ($object->textbox1 === '') {
				$object->textbox1 = $xx;
			}
		}

		return implode("\n\n", $boxes);
	}


}

