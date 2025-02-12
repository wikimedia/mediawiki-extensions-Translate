<?php
/**
 * System account to handle user related modifications
 *
 * @file
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\Translate\SystemUsers;

use MediaWiki\User\User;

/** @since 2019.08 */
class TranslateUserManager {
	public static function getUser(): User {
		return User::newSystemUser( self::getName(), [ 'steal' => true ] );
	}

	public static function getName(): string {
		global $wgTranslateUserManagerName;

		return $wgTranslateUserManagerName;
	}
}
