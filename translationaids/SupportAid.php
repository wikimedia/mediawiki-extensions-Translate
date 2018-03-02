<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Translation aid which gives an url where users can ask for help
 *
 * @ingroup TranslationAids
 * @since 2013-01-02
 */
class SupportAid extends TranslationAid {
	public function getData() {
		return [
			'url' => self::getSupportUrl( $this->handle->getTitle() ),
		];
	}

	/**
	 * Target URL for a link provided by a support button/aid.
	 *
	 * @param Title $title Title object for the translation message.
	 * @since 2015.09
	 * @return string
	 * @throws TranslationHelperException
	 */
	public static function getSupportUrl( Title $title ) {
		global $wgTranslateSupportUrl, $wgTranslateSupportUrlNamespace;
		$namespace = $title->getNamespace();

		// Fetch the configuration for this namespace if possible, or the default.
		if ( isset( $wgTranslateSupportUrlNamespace[$namespace] ) ) {
			$config = $wgTranslateSupportUrlNamespace[$namespace];
		} elseif ( $wgTranslateSupportUrl ) {
			$config = $wgTranslateSupportUrl;
		} else {
			throw new TranslationHelperException( 'Support page not configured' );
		}

		// Preprocess params
		$params = [];
		if ( isset( $config['params'] ) ) {
			foreach ( $config['params'] as $key => $value ) {
				$params[$key] = str_replace( '%MESSAGE%', $title->getPrefixedText(), $value );
			}
		}

		// Return the URL or make one from the page
		if ( isset( $config['url'] ) ) {
			return wfAppendQuery( $config['url'], $params );
		} elseif ( isset( $config['page'] ) ) {
			$page = Title::newFromText( $config['page'] );
			if ( !$page ) {
				throw new TranslationHelperException( 'Support page not configured properly' );
			}
			return $page->getFullURL( $params );
		} else {
			throw new TranslationHelperException( 'Support page not configured properly' );
		}
	}
}
