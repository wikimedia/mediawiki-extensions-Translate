<?php
/**
 * Contains classes for addition of extension specific preference settings.
 *
 * @file
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010 Siebrand Mazeland, Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Utilities\HTMLJsSelectToInputField;
use MediaWiki\Extension\Translate\Utilities\JsSelectToInput;
use MediaWiki\MediaWikiServices;

/**
 * Class to add Translate specific preference settings.
 */
class TranslatePreferences {
	/**
	 * Add 'translate-editlangs' preference.
	 * These are the languages also shown when translating.
	 *
	 * @param User $user
	 * @param array &$preferences
	 * @return bool true
	 */
	public static function translationAssistLanguages( User $user, &$preferences ) {
		// Get selector.
		$select = self::languageSelector();
		// Set target ID.
		$select->setTargetId( 'mw-input-translate-editlangs' );
		// Get available languages.
		$languages = MediaWikiServices::getInstance()->getLanguageNameUtils()->getLanguageNames();

		$preferences['translate-editlangs'] = [
			'class' => HTMLJsSelectToInputField::class,
			// prefs-translate
			'section' => 'editing/translate',
			'label-message' => 'translate-pref-editassistlang',
			'help-message' => 'translate-pref-editassistlang-help',
			'select' => $select,
			'valid-values' => array_keys( $languages ),
			'name' => 'translate-editlangs',
		];

		return true;
	}

	/**
	 * JavsScript selector for language codes.
	 * @return JsSelectToInput
	 */
	protected static function languageSelector() {
		$lang = RequestContext::getMain()->getLanguage();
		$languages = MediaWikiServices::getInstance()->getLanguageNameUtils()->getLanguageNames( $lang->getCode() );
		ksort( $languages );

		$selector = new XmlSelect( false, 'mw-language-selector' );
		foreach ( $languages as $code => $name ) {
			$selector->addOption( "$code - $name", $code );
		}

		$jsSelect = new JsSelectToInput( $selector );

		return $jsSelect;
	}
}
