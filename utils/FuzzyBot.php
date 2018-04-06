<?php
/**
 * Do it all maintenance account
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * FuzzyBot - the misunderstood workhorse.
 * @since 2012-01-02
 */
class FuzzyBot {
	public static function getUser() {
		return User::newSystemUser( self::getName(), [ 'steal' => true ] );
	}

	public static function getName() {
		global $wgTranslateFuzzyBotName;

		return $wgTranslateFuzzyBotName;
	}
}
