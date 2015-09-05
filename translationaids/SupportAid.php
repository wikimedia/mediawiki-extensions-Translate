<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Translation aid which gives an url where users can ask for help
 *
 * @ingroup TranslationAids
 * @since 2013-01-02
 */
class SupportAid extends TranslationAid {
	public function getData() {
		return array(
			'url' => SupportAid::getSupportUrl( $this->handle->getTitle() ),
		);
	}

	/**
	* Target URL for a link provided by a support button/aid.
	*
	* @param $title Title Title object for the translatable message.
	* @since 2015.09
	*/
	public static function getSupportUrl( $title = null ) {
		global $wgTranslateSupportUrl;
		if ( !$wgTranslateSupportUrl ) {
			throw new TranslationHelperException( "Support page not configured" );
		} else {
			$supportTitle = Title::newFromText( $wgTranslateSupportUrl['page'] );
			$supportParams = $wgTranslateSupportUrl['params'];
		}

		if ( $supportTitle ) {
			foreach ( $supportParams as &$value ) {
				$value = str_replace( '%MESSAGE%', $title->getPrefixedText(), $value );
			}
			return $supportTitle->getFullUrl( $supportParams );
		} else {
			throw new TranslationHelperException( "Support page not configured properly" );
		}
	}

}
