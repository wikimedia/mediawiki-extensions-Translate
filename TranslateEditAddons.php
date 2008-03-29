<?php
if (!defined('MEDIAWIKI')) die();

/**
 * Tools for edit page view to aid translators.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2007-2008 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
class TranslateEditAddons {
	const MSG = 'translate-edit-';

	static function addTools( $object ) {
		if( $object->mTitle->getNamespace() === NS_MEDIAWIKI ) {
			$object->editFormTextTop .= self::editBoxes( $object );
		}
		return true;
	}

	private static function getFallbacks( $code ) {
		global $wgTranslateLanguageFallbacks, $wgTranslateDocumentationLanguageCode;

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

	private static function doBox( $msg, $code, $title = false ) {
		global $wgUser, $wgLang;
		if (!$msg ) { return ''; }

		$name = TranslateUtils::getLanguageName( $code, false, $wgLang->getCode() );
		$code = strtolower( $code );

		$attributes = array();
		if ( !$title ) {
			$attributes['class'] = 'mw-sp-translate-in-other-big';
		} elseif ( $code === 'en' ) {
			$attributes['class'] = 'mw-sp-translate-edit-definition';
		} else {
			$attributes['class'] = 'mw-sp-translate-edit-committed';
		}
		if ( mb_strlen( $msg ) < 100 && !$title ) {
			$attributes['class'] = 'mw-sp-translate-in-other-small';
		}

		$msg = TranslateUtils::convertWhiteSpaceToHTML( $msg );

		if ( !$title ) $title = "$name ($code)";
		$title = htmlspecialchars( $title );

		return TranslateUtils::fieldset( $title, Xml::tags( 'code', null, $msg ), $attributes );
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
		wfLoadExtensionMessages( 'Translate' );
		global $wgTranslateDocumentationLanguageCode, $wgOut;

		list( $key, $code ) = self::figureMessage( $object );

		$group = self::getMessageGroup( $key );
		if ( $group === null ) return;

		$en = $group->getMessage( $key, 'en' );
		$xx = $group->getMessage( $key, $code );

		$boxes = array();

		// In other languages (if any)
		$inOtherLanguages = array();
		foreach ( self::getFallbacks( $code ) as $fbcode ) {
			$fb = $group->getMessage( $key, $fbcode );
			/* For fallback, even uncommitted translation may be useful */
			if ( $fb === null ) {
				$fb = TranslateUtils::getMessageContent( $key, $fbcode );
			}
			if ( $fb !== null ) {
				$inOtherLanguages[] = self::dobox( $fb, $fbcode );
			}
		}
		if ( count($inOtherLanguages) ) {
			$boxes[] = TranslateUtils::fieldset( wfMsgHtml( self::MSG . 'in-other-languages' ),
				implode( "\n", $inOtherLanguages ), array( 'class' => 'mw-sp-translate-edit-inother' ) );
		}

		// User provided documentation
		if ( $wgTranslateDocumentationLanguageCode ) {
			global $wgUser;
			$title = Title::makeTitle( NS_MEDIAWIKI, $key . '/' . $wgTranslateDocumentationLanguageCode );
			$edit = $wgUser->getSkin()->makeKnownLinkObj( $title, wfMsgHtml( self::MSG . 'contribute' ), 'action=edit' );
			$info = TranslateUtils::getMessageContent( $key, $wgTranslateDocumentationLanguageCode );
			if ( !$info ) {
				$info = wfMsg( self::MSG . 'no-information' );
			}
			$boxes[] = TranslateUtils::fieldset(
				wfMsgHtml( self::MSG . 'information', $edit ), $wgOut->parse( $info, false ),
				$info ? array( 'class' => 'mw-sp-translate-edit-info' ) : array( 'id' => 'mw-sp-translate-edit-noinfo' )
			);
		}

		// Current committed translation
		// Should this be higher up, because it's not as importad as definition for example
		if ( $xx !== null && $code !== 'en' ) {
			//$boxes[] = self::dobox( $xx, $code, wfMsg( self::MSG . 'committed' ) );

			// Append translation from the file to edit area, if it's empty.
			if ($object->firsttime && $object->textbox1 === '') {
				$object->textbox1 = $xx;
			}
		}

		// Definition
		if ( $en !== null ) {
			$boxes[] = self::doBox( $en, 'en', wfMsg( self::MSG . 'definition' ) );
		}


		// Some syntactic checks
		$translation = $object->textbox1 ? $object->textbox1 : $xx;
		if ( $translation ) {
			$message = new TMessage( $key, $en );
			$message->database = $translation;
			$checks = MessageChecks::doChecks( $message );
			if ( count($checks) ) {
				$boxes[] = TranslateUtils::fieldset( wfMsgHtml( self::MSG . 'warnings' ), implode( '<hr />', $checks),
					array( 'class' => 'mw-sp-translate-edit-warnings' ) );
			}
		}

		$group->reset();
		return implode("\n\n", $boxes);
	}


}
