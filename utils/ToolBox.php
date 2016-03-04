<?php
/**
 * Classes for adding extension specific toolbox menu items.
 *
 * @file
 * @author Siebrand Mazeland
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2010, Siebrand Mazeland, Niklas Laxström
 * @license GPL-2.0+
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
	 * @param $quickTemplate QuickTemplate
	 *
	 * @return bool
	 */
	public static function toolboxAllTranslations( &$quickTemplate ) {
		$title = $quickTemplate->getSkin()->getTitle();
		$handle = new MessageHandle( $title );
		if ( $handle->isValid() ) {
			$message = $title->getNsText() . ':' . $handle->getKey();
			$desc = wfMessage( 'translate-sidebar-alltrans' )->escaped();
			$url = htmlspecialchars( SpecialPage::getTitleFor( 'Translations' )
				->getLocalURL( array( 'message' => $message ) ) );

			// Add the actual toolbox entry.
			// Add newlines and tabs for nicer HTML output.
			echo "\n\t\t\t\t<li id=\"t-alltrans\"><a href=\"$url\">$desc</a></li>\n";
		}

		return true;
	}
}
