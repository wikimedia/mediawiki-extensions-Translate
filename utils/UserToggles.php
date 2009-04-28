<?php
if ( !defined( 'MEDIAWIKI' ) ) die();

class TranslatePreferences {
	/**
	 * Add preferences for Translate
	 */
	public static function onGetPreferences( $user, &$preferences ) {
		global $wgEnableEmail, $wgUser;

		if ( $wgEnableEmail && $wgUser->isEmailConfirmed() ) {
			// 'translate-pref-nonewsletter' is used as opt-out for
			// users with a confirmed e-mail address
			$prefs = array(
				'translate' => array(
					'type' => 'toggle',
					'section' => 'personal/e-mail',
					'label-message' => 'translate-pref-nonewsletter'
				)
			);

			// Add setting after 'enotifrevealaddr'
			$preferences = wfArrayInsertAfter( $preferences, $prefs, 'enotifrevealaddr' );
		}

		return true;
	}
}
