<?php
/**
 * Classes for adding extension specific toolbox menu items.
 *
 * @file
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Siebrand Mazeland, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Adds toolbox menu item to Special:Prefixindex to show all other
 * available translations for a message. Only shown when it
 * actually is a translatable/translated message.
 */
class TranslateToolbox {
	/**
	 * @param Skin $skin
	 * @param array &$sidebar Array with sidebar items
	 *
	 * @return void
	 */
	public static function toolboxAllTranslations( Skin $skin, array &$sidebar ): void {
		$title = $skin->getTitle();
		$handle = new MessageHandle( $title );

		if ( !$handle->isValid() ) {
			return;
		}

		$message = $title->getNsText() . ':' . $handle->getKey();
		$url = $skin::makeSpecialUrl( 'Translations', [ 'message' => $message ] );

		// Add the actual toolbox entry.
		$sidebar['TOOLBOX'][ 'alltrans' ] = [
			'href' => $url,
			'id' => 't-alltrans',
			'msg' => 'translate-sidebar-alltrans',
		];
	}
}
