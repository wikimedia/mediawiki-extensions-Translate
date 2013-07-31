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
 * Translation aid which gives an url where users can ask for hlep
 *
 * @ingroup TranslationAids
 * @since 2013-01-02
 */
class SupportAid extends TranslationAid {
	public function getData() {
		global $wgTranslateSupportUrl;
		if ( !$wgTranslateSupportUrl ) {
			throw new TranslationHelperException( "Support page not configured" );
		}

		$supportTitle = Title::newFromText( $wgTranslateSupportUrl['page'] );
		if ( !$supportTitle ) {
			throw new TranslationHelperException( "Support page not configured properly" );
		}

		$supportParams = $wgTranslateSupportUrl['params'];
		$title = $this->handle->getTitle();
		foreach ( $supportParams as &$value ) {
			$value = str_replace( '%MESSAGE%', $title->getPrefixedText(), $value );
		}

		return array(
			'url' => $supportTitle->getFullUrl( $supportParams ),
		);
	}
}
