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
		global $wgTranslateSupportUrl, $wgTranslateSupportUrlNamespace;
		$title = $this->handle->getTitle();
		$namespace = $title->getNamespace();

		// Fetch the configuration for this namespace if possible, or the default.
		if ( isset( $wgTranslateSupportUrlNamespace[$namespace] ) ) {
			$config = $wgTranslateSupportUrlNamespace[$namespace];
		} elseif ( $wgTranslateSupportUrl ) {
			$config = $wgTranslateSupportUrl;
		} else {
			throw new TranslationHelperException( "Support page not configured" );
		}

		if ( isset( $config['page'] ) ) {
			$supportTitle = Title::newFromText( $config['page'] );
		} else {
			$supportTitle = false;
		}
		if ( isset( $config['params'] ) ) {
			$params = $config['params'];
			foreach ( $params as &$value ) {
				$value = str_replace( '%MESSAGE%', $title->getPrefixedText(), $value );
			}
		} else {
			$params = array();
		}
		if ( isset( $config['url'] ) ) {
			$urlBase = $config['url'];
		} else {
			$urlBase = false;
		}

		if ( $urlBase ) {
			return wfAppendQuery( $urlBase, $params );
		} elseif ( $supportTitle ) {
			return $supportTitle->getFullUrl( $params );
		} else {
			throw new TranslationHelperException( "Support page not configured properly" );
		}
	}

}
