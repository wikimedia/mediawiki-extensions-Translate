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
 * Adds extension specific context aware toolbox menu items.
 */
class TranslateToolbox {
	/**
	 * Adds link in toolbox to Special:Prefixindex to show all other
	 * available translations for a message. Only shown when it
	 * actually is a translatable/translated message.
	 *
	 * @param BaseTemplate $baseTemplate The base skin template
	 * @param array &$toolbox An array of toolbox items
	 *
	 * @return bool
	 */
	public static function toolboxAllTranslations( $baseTemplate, &$toolbox ) {
		$title = $baseTemplate->getSkin()->getTitle();
		$handle = new MessageHandle( $title );
		if ( $handle->isValid() ) {
			$message = $title->getNsText() . ':' . $handle->getKey();
			$url = SpecialPage::getTitleFor( 'Translations' )
				->getLocalURL( [ 'message' => $message ] );

			// Add the actual toolbox entry.
			$toolbox[ 'alltrans' ] = [
				'href' => $url,
				'id' => 't-alltrans',
				'msg' => 'translate-sidebar-alltrans',
			];
		}

		return true;
	}
}
