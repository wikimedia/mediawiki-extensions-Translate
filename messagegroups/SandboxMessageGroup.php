<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashStorage;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

/**
 * @since 2013.06
 * @ingroup MessageGroup
 */
class SandboxMessageGroup extends WikiMessageGroup {
	/**
	 * Yes this is very ugly hack and should not be removed.
	 * @see \MediaWiki\Extension\Translate\MessageLoading\MessageCollection::getPages()
	 * @var int|false
	 */
	protected $namespace = false;
	/** @var string */
	protected $language;

	/**
	 * #setLanguage must be called before calling getDefinitions.
	 */
	public function __construct() {
	}

	public function setLanguage( $code ) {
		$this->language = $code;
	}

	public function getId() {
		return '!sandbox';
	}

	public function getLabel( IContextSource $context = null ) {
		// Should not be visible
		return 'Sandbox messages';
	}

	public function getDescription( IContextSource $context = null ) {
		// Should not be visible
		return 'Suggests messages to translate for sandboxed users';
	}

	public function getDefinitions() {
		global $wgTranslateSandboxLimit;

		// This will contain the list of messages shown to the user
		$list = [];

		// Ugly
		$store = new TranslationStashStorage(
			MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_PRIMARY )
		);
		$user = RequestContext::getMain()->getUser();
		$translations = $store->getTranslations( $user );

		// Add messages the user has already translated first, so he
		// can go back and correct them.
		foreach ( $translations as $translation ) {
			$title = $translation->getTitle();
			$handle = new MessageHandle( $title );
			$index = $title->getNamespace() . ':' . $handle->getKey();
			$list[$index] = '';
		}

		// Get some random keys
		$all = Services::getInstance()->getMessageIndex()->getKeys();
		// In case there aren't any messages
		if ( $all === [] ) {
			return $list;
		}
		$min = 0;
		$max = count( $all ) - 1; // Indexes are zero-based

		// Get some message. Will be filtered to less below.
		for ( $i = count( $list ); $i < 100; $i++ ) {
			$list[$all[rand( $min, $max )]] = '';
		}

		// Fetch definitions, slowly, one by one
		$count = 0;

		// Provide twice the number of messages than the limit
		// to have a buffer in case the user skips some messages
		$messagesToProvide = $wgTranslateSandboxLimit * 2;

		foreach ( $list as $index => &$translation ) {
			[ $ns, $page ] = explode( ':', $index, 2 );
			$title = Title::makeTitle( (int)$ns, "$page/{$this->language}" );
			$handle = new MessageHandle( $title );

			if ( MessageGroups::isTranslatableMessage( $handle, $this->language ) ) {
				// Modified by reference
				$translation = $this->getMessageContent( $handle );
				if ( $translation === null ) {
					// Something is not in sync or badly broken. Handle gracefully.
					unset( $list[$index] );
					wfWarn( "No message definition for $index while preparing the sandbox" );

					continue;
				}
			} else {
				// This might include messages that the user has already translated
				// or just dated message index.
				unset( $list[$index] );

				continue;
			}

			$count++;

			if ( $count === $messagesToProvide ) {
				break;
			}
		}

		// Remove the extra entries
		$list = array_slice( $list, 0, $messagesToProvide );

		return $list;
	}

	public function getValidator() {
		return null;
	}

	/**
	 * Subpage language code, if any in the title, is ignored.
	 * @param MessageHandle $handle
	 * @return null|string
	 */
	public function getMessageContent( MessageHandle $handle ) {
		$groupId = Services::getInstance()->getMessageIndex()->getPrimaryGroupId( $handle );
		if ( $groupId === null ) {
			return null;
		}
		$group = MessageGroups::getGroup( $groupId );
		$key = $handle->getKey();

		$source = $group->getMessage( $key, $group->getSourceLanguage() );
		if ( $source !== null ) {
			return $source;
		}

		// Try harder
		$keys = $group->getKeys();

		// Try to find the original key with correct case
		foreach ( $keys as $realkey ) {
			if ( $key === strtolower( $realkey ) ) {
				$key = $realkey;
				break;
			}
		}

		return $group->getMessage( $key, $group->getSourceLanguage() );
	}
}
