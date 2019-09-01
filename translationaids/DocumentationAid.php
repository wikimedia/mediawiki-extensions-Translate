<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Translation aid which gives the message documentation.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01
 */
class DocumentationAid extends TranslationAid {
	public function getData() {
		global $wgTranslateDocumentationLanguageCode;
		if ( !$wgTranslateDocumentationLanguageCode ) {
			throw new TranslationHelperException( 'Message documentation is disabled' );
		}

		$page = $this->handle->getKey();
		$ns = $this->handle->getTitle()->getNamespace();

		$info = TranslateUtils::getMessageContent( $page, $wgTranslateDocumentationLanguageCode, $ns );

		return [
			'language' => MediaWikiServices::getInstance()->getContentLanguage()->getCode(),
			'value' => $info,
			'html' => TranslateUtils::parseAsInterface(
				$this->context->getOutput(), $info
			),
		];
	}
}
