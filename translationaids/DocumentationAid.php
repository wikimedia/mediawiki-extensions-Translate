<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Translation aid which gives the message documentation.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01
 */
class DocumentationAid extends TranslationAid {
	public function getData() {
		global $wgTranslateDocumentationLanguageCode, $wgContLang;
		if ( !$wgTranslateDocumentationLanguageCode ) {
			throw new TranslationHelperException( 'Message documentation is disabled' );
		}

		$page = $this->handle->getKey();
		$ns = $this->handle->getTitle()->getNamespace();

		$info = TranslateUtils::getMessageContent( $page, $wgTranslateDocumentationLanguageCode, $ns );

		return array(
			'language' => $wgContLang->getCode(),
			'value' => $info,
			'html' => $this->context->getOutput()->parse( $info ),
		);
	}
}
