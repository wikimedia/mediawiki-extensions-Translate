<?php
/**
 * Contains classes for addition of extension specific preference settings.
 *
 * @file
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010 Siebrand Mazeland, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Class to add Translate specific preference settings.
 */
class TranslatePreferences {
	/**
	 * Add 'translate-pref-nonewsletter' preference.
	 * This is most probably specific to translatewiki.net. Can be enabled
	 * with $wgTranslateNewsletterPreference.
	 *
	 * @param $user User
	 * @param $preferences array
	 * @return bool true
	 */
	public static function onGetPreferences( $user, &$preferences ) {
		global $wgTranslateNewsletterPreference;

		if ( !$wgTranslateNewsletterPreference ) {
			return true;
		}

		global $wgEnableEmail, $wgEnotifRevealEditorAddress;

		// Only show if email is enabled and user has a confirmed email address.
		if ( $wgEnableEmail && $user->isEmailConfirmed() ) {
			// 'translate-pref-nonewsletter' is used as opt-out for
			// users with a confirmed email address
			$prefs = array(
				'translate-nonewsletter' => array(
					'type' => 'toggle',
					'section' => 'personal/email',
					'label-message' => 'translate-pref-nonewsletter'
				)
			);

			// Add setting after 'enotifrevealaddr'.
			$preferences = wfArrayInsertAfter( $preferences, $prefs,
				$wgEnotifRevealEditorAddress ? 'enotifrevealaddr' : 'enotifminoredits' );
		}

		return true;
	}

	/**
	 * Add 'translate-editlangs' preference.
	 * These are the languages also shown when translating.
	 *
	 * @param User $user
	 * @param array $preferences
	 * @return bool true
	 */
	public static function translationAssistLanguages( User $user, &$preferences ) {
		// Get selector.
		$select = self::languageSelector();
		// Set target ID.
		$select->setTargetId( 'mw-input-translate-editlangs' );
		// Get available languages.
		$languages = Language::fetchLanguageNames();

		$preferences['translate-editlangs'] = array(
			'class' => 'HTMLJsSelectToInputField',
			'section' => 'editing/translate',
			'label-message' => 'translate-pref-editassistlang',
			'help-message' => 'translate-pref-editassistlang-help',
			'select' => $select,
			'valid-values' => array_keys( $languages ),
			'name' => 'translate-editlangs',
		);

		return true;
	}

	/**
	 * JavsScript selector for language codes.
	 * @return JsSelectToInput
	 */
	protected static function languageSelector() {
		if ( is_callable( array( 'LanguageNames', 'getNames' ) ) ) {
			$lang = RequestContext::getMain()->getLanguage();
			$languages = LanguageNames::getNames( $lang->getCode(),
				LanguageNames::FALLBACK_NORMAL
			);
		} else {
			$languages = Language::fetchLanguageNames();
		}

		ksort( $languages );

		$selector = new XmlSelect( 'mw-language-selector', 'mw-language-selector' );
		foreach ( $languages as $code => $name ) {
			$selector->addOption( "$code - $name", $code );
		}

		$jsSelect = new JsSelectToInput( $selector );
		$jsSelect->setSourceId( 'mw-language-selector' );

		return $jsSelect;
	}
}
