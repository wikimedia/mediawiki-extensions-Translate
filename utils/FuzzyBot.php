<?php
/**
 * Do it all maintenance account
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * FuzzyBot - the misunderstood workhorse.
 * @since 2012-01-02
 */
class FuzzyBot {
	public static function getUser() {
		if ( method_exists( 'User', 'newSystemUser' ) ) {
			return User::newSystemUser( self::getName(), array( 'steal' => true ) );
		}

		// BC for MW < 1.27
		$bot = User::newFromName( self::getName() );
		if ( $bot->isAnon() ) {
			$bot->addToDatabase();
		}

		return $bot;
	}

	public static function getName() {
		global $wgTranslateFuzzyBotName;

		return $wgTranslateFuzzyBotName;
	}
}
