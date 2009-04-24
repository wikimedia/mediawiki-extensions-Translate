<?php
if ( !defined( 'MEDIAWIKI' ) ) die();

class TranslatePreferences {
	/**
	 * Add preferences for Translate
	 */
	public static function onGetPreferences( $user, &$preferences ) {
		// 'translate-pref-nonewsletter' is used as opt-out for
		// users with a confirmed e-mail address
		$preferences['translate'] =
			array(
				'type' => 'toggle',
				'section' => 'misc',
				'label-message' => 'translate-pref-nonewsletter',
			);

		return true;
	}
}
