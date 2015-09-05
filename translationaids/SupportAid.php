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
			'url' => SupportAid::getSupportUrl(),
		);
	}

	/**
	* Target URL for a link provided by a support button/aid.
	*
	* @param $title Title|null Optional Title object for the translatable message.
	* @since 2015-09
	*/
	static function getSupportUrl( $title = null ) {
		$title = $this->handle->getTitle();
		$namespace = $title->getNamespace();

		// Fetch the configuration for this namespace if possible, or the default.
		global $wgTranslateSupportUrl, $wgTranslateSupportUrlNamespace;
		$supportConfig = false;
		if ( $wgTranslateSupportUrlNamespace &&
				array_key_exists( $namespace, $wgTranslateSupportUrlNamespace ) ) {
			$supportConfig = $wgTranslateSupportUrlNamespace;
		elseif ( $wgTranslateSupportUrl ) {
			$supportConfig = $wgTranslateSupportUrl;
		} else {
			throw new TranslationHelperException( "Support page not configured" );
		}
		
		if ( array_key_exists( 'page', $supportConfig ) {
			$supportTitle = Title::newFromText( $supportConfig['page'] );
		} else {
			$supportTitle = false;
		}
		if ( array_key_exists( 'params', $supportConfig ) {
			$supportParams = $supportConfig['params'];
			foreach ( $supportParams as &$value ) {
				$value = str_replace( '%MESSAGE%', $title->getPrefixedText(), $value );
			}
		} else {
			$supportParams = false;
		}
		if ( array_key_exists( 'url', $supportConfig ) {
			$supportUrlBase = $supportConfig['url'];
		} else {
			$supportUrlBase = false;
		}
		
		if ( $supportUrlBase && $supportParams ) {
			return wfAppendQuery( $supportUrlBase, $supportParams );
		} elseif ( $supportTitle && $supportParams ) {
			return $supportTitle->getFullUrl( $supportParams );
		} else {
			throw new TranslationHelperException( "Support page not configured properly" );
		}
	}

}
