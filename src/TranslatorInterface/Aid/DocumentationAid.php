<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface\Aid;

use MediaWiki\Extension\Translate\TranslatorInterface\TranslationHelperException;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\MediaWikiServices;

/**
 * Translation aid that provides the message documentation.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-01
 * @ingroup TranslationAids
 */
class DocumentationAid extends TranslationAid {
	public function getData(): array {
		global $wgTranslateDocumentationLanguageCode;
		if ( !$wgTranslateDocumentationLanguageCode ) {
			throw new TranslationHelperException( 'Message documentation is disabled' );
		}

		$page = $this->handle->getKey();
		$ns = $this->handle->getTitle()->getNamespace();

		$info = Utilities::getMessageContent( $page, $wgTranslateDocumentationLanguageCode, $ns ) ?? '';

		return [
			'language' => MediaWikiServices::getInstance()->getContentLanguageCode()->toString(),
			'value' => $info,
			'html' => $this->context->getOutput()->parseAsInterface( $info )
		];
	}
}
