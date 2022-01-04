<?php
declare( strict_types = 1 );

/**
 * Message group for MediaWiki extensions.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup MessageGroup
 */
class MediaWikiExtensionMessageGroup extends FileBasedMessageGroup {
	/**
	 * MediaWiki extensions all should have key in their i18n files
	 * describing them. This override method implements the logic
	 * to retrieve them.
	 * @param IContextSource|null $context
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

		return $desc;
	}
}
