<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use MediaWiki\Extension\Translate\TranslatorInterface\TranslationHelperException;
use MessageHandle;
use Title;
use TranslateUtils;

/**
 * Translation aid that provides an url where users can ask for help
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-02
 * @ingroup TranslationAids
 */
class SupportAid extends TranslationAid {
	public function getData(): array {
		return [
			'url' => self::getSupportUrl( $this->handle ),
		];
	}

	/**
	 * Target URL for a link provided by a support button/aid.
	 * @param MessageHandle $handle MessageHandle object for the translation message.
	 * @return string
	 * @throws TranslationHelperException
	 */
	public static function getSupportUrl( MessageHandle $handle ): string {
		$title = $handle->getTitle();
		$config = self::getConfig( $handle );

		$placeholders = [
			'%MESSAGE%' => $title->getPrefixedText(),
			'%MESSAGE_URL%' => TranslateUtils::getEditorUrl( $handle )
		];

		// Preprocess params
		$params = [];
		if ( isset( $config['params'] ) ) {
			foreach ( $config['params'] as $key => $value ) {
				$params[$key] = strtr( $value, $placeholders );
			}
		}

		// Return the URL or make one from the page
		if ( isset( $config['url'] ) ) {
			return wfAppendQuery( $config['url'], $params );
		} elseif ( isset( $config['page'] ) ) {
			$page = Title::newFromText( $config['page'] );
			if ( $page ) {
				return $page->getFullURL( $params );
			}
		}

		throw new TranslationHelperException( 'Support page not configured properly' );
	}

	/**
	 * Fetches Support URL config
	 * @param MessageHandle $handle
	 * @return array
	 * @throws TranslationHelperException
	 */
	private static function getConfig( MessageHandle $handle ): array {
		global $wgTranslateSupportUrl, $wgTranslateSupportUrlNamespace;

		if ( !$handle->isValid() ) {
			throw new TranslationHelperException( 'Invalid MessageHandle' );
		}

		// Fetch group level configuration if possible, fallback to namespace based, or default
		$group = $handle->getGroup();
		$namespace = $handle->getTitle()->getNamespace();
		$config = $group->getSupportConfig()
			?? $wgTranslateSupportUrlNamespace[$namespace]
			?? $wgTranslateSupportUrl;

		if ( !$config ) {
			throw new TranslationHelperException( 'Support page not configured' );
		}

		return $config;
	}
}
