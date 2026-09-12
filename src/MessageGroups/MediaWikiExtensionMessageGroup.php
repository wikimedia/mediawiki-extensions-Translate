<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroups;

use MediaWiki\Context\IContextSource;

/**
 * Message group for MediaWiki extensions.
 * @since 2026.09
 * @author Niklas Laxström
 * @author Siebrand Mazeland
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
	public function getDescription( ?IContextSource $context = null ) {
		$language = $context?->getLanguage()->getCode() ?? $this->getSourceLanguage();

		$msgkey = $this->conf['BASIC']['descriptionmsg'] ?? null;
		$desc = '';
		if ( $msgkey !== null ) {
			$desc = $this->getMessage( $msgkey, $language );
			if ( $desc === null || $desc === '' ) {
				$desc = $this->getMessage( $msgkey, $this->getSourceLanguage() );
			}
		}

		if ( $desc === null || $desc === '' ) {
			// That failed, default to 'description'
			$desc = parent::getDescription( $context );
		}

		return $desc;
	}
}

/** @deprecated class alias since 2026.09 */
class_alias( MediaWikiExtensionMessageGroup::class, 'MediaWikiExtensionMessageGroup' );
