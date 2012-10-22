<?php
/**
 * This file a contains a message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Message group for %MediaWiki extensions.
 * @ingroup MessageGroup
 */
class MediaWikiExtensionMessageGroup extends SingleFileBasedMessageGroup {
	/**
	 * MediaWiki extensions all should have key in their i18n files
	 * describing them. This override method implements the logic
	 * to retrieve them. Also urls are included if available.
	 * Needs the configure extension.
	 */
	public function getDescription( IContextSource $context = null ) {

		$language = $this->getSourceLanguage();
		if ( $context ) {
			$language = $context->getLanguage()->getCode();
		}

		$msgkey = $this->getFromConf( 'BASIC', 'descriptionmsg' );
		if ( $msgkey ) {
			$desc = $this->getMessage( $msgkey, $language );
			if ( strval( $desc ) === '' ) {
				$desc = $this->getMessage( $msgkey, $this->getSourceLanguage() );
			}
		}

		if ( strval( $desc ) === '' ) {
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
