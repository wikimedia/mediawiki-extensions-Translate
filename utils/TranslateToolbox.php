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
	 * This handler will be called for MW < 1.35
	 *
	 * @param BaseTemplate $baseTemplate The base skin template
	 * @param array &$toolbox An array of toolbox items
	 *
	 * @return void
	 */
	public static function toolboxAllTranslationsOld(
		BaseTemplate $baseTemplate, array &$toolbox
	): void {
		$skin = $baseTemplate->getSkin();
		$title = $skin->getTitle();
		$handle = new MessageHandle( $title );

		if ( !$handle->isValid() ) {
			return;
		}

		$message = $title->getNsText() . ':' . $handle->getKey();
		$url = $skin::makeSpecialUrl( 'Translations', [ 'message' => $message ] );

		// Add the actual toolbox entry.
		$toolbox[ 'alltrans' ] = [
			'href' => $url,
			'id' => 't-alltrans',
			'msg' => 'translate-sidebar-alltrans',
		];
	}

	/**
	 * This handler will be called for MW >= 1.35
	 *
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
