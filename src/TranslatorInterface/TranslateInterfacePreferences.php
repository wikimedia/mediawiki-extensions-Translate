<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\Utilities\HTMLJsSelectToInputField;
use MediaWiki\Extension\Translate\Utilities\JsSelectToInput;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;
use MediaWiki\Xml\XmlSelect;

/**
 * Contains classes for addition of extension specific preference settings.
 *
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010 Siebrand Mazeland, Niklas Laxström
 * @license GPL-2.0-or-later
 */
class TranslateInterfacePreferences {
	/**
	 * Add 'translate-editlangs' preference.
	 * These are the languages also shown when translating.
	 */
	public static function translationAssistLanguages( User $user, array &$preferences ): void {
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
	}

	/** JavaScript selector for language codes. */
	private static function languageSelector(): JsSelectToInput {
		$lang = RequestContext::getMain()->getLanguage();
		$languages = MediaWikiServices::getInstance()->getLanguageNameUtils()->getLanguageNames( $lang->getCode() );
		ksort( $languages );

		$selector = new XmlSelect( false, 'mw-language-selector' );
		foreach ( $languages as $code => $name ) {
			$selector->addOption( "$code - $name", $code );
		}

		return new JsSelectToInput( $selector );
	}
}
