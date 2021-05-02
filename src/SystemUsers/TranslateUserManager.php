<?php
/**
 * System account to handle user related modifications
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\SystemUsers;

use User;

/** @since 2019.08 */
class TranslateUserManager {
	public static function getUser() {
		return User::newSystemUser( self::getName(), [ 'steal' => true ] );
	}

	public static function getName() {
		global $wgTranslateUserManagerName;

		return $wgTranslateUserManagerName;
	}
}
