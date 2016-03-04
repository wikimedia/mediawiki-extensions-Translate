<?php
/**
 * This file a contains a message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Message group for %MediaWiki extensions.
 * @ingroup MessageGroup
 */
class MediaWikiExtensionMessageGroup extends FileBasedMessageGroup {
	/**
	 * MediaWiki extensions all should have key in their i18n files
	 * describing them. This override method implements the logic
	 * to retrieve them. Also URLs are included if available.
	 * Needs the Configure extension.
	 * @param IContextSource $context
	 * @return string
	 */
	public function getDescription( IContextSource $context = null ) {
		$language = $this->getSourceLanguage();
		if ( $context ) {
			$language = $context->getLanguage()->getCode();
		}

		$msgkey = $this->getFromConf( 'BASIC', 'descriptionmsg' );
		$desc = '';
		if ( $msgkey ) {
			$desc = $this->getMessage( $msgkey, $language );
			if ( (string)$desc === '' ) {
				$desc = $this->getMessage( $msgkey, $this->getSourceLanguage() );
			}
		}

		if ( (string)$desc === '' ) {
			// That failed, default to 'description'
			$desc = parent::getDescription( $context );
		}

		$url = $this->getFromConf( 'BASIC', 'extensionurl' );
		if ( $url ) {
			$desc .= "\n\n$url";
		}

		return $desc;
	}
}
