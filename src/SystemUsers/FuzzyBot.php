<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\SystemUsers;

use User;

/**
 * FuzzyBot - the misunderstood workhorse.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2012-01-02
 */
class FuzzyBot {
	public static function getUser(): User {
		return User::newSystemUser( self::getName(), [ 'steal' => true ] );
	}

	public static function getName(): string {
		global $wgTranslateFuzzyBotName;

		return $wgTranslateFuzzyBotName;
	}
}
