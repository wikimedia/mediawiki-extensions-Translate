<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use MediaWiki\Extension\Translate\Utilities\HTMLTranslationAssistLanguagesField;
use MediaWiki\MediaWikiServices;
use MediaWiki\User\User;

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
		$languages = MediaWikiServices::getInstance()->getLanguageNameUtils()->getLanguageNames();

		$preferences['translate-editlangs'] = [
			'class' => HTMLTranslationAssistLanguagesField::class,
			'useCodex' => true,
			'multiple' => true,
			'section' => 'editing/translate',
			'label-message' => 'translate-pref-editassistlang',
			'help-message' => 'translate-pref-editassistlang-help',
			'name' => 'translate-editlangs[]',
			'languages' => $languages,
		];
	}
}
