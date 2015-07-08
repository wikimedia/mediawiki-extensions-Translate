<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * @since 2013.06
 * @ingroup MessageGroup
 */
class SandboxMessageGroup extends WikiMessageGroup {
	/*
	 * Yes this is very ugly hack and should not be removed.
	 * @see MessageCollection::getPages()
	 */
	protected $namespace = false;

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
		global $wgTranslateSandboxSuggestions, $wgTranslateSandboxLimit;

		// This will contain the list of messages shown to the user
		$list = array();

		// Ugly
		$store = new TranslationStashStorage( wfGetDB( DB_MASTER ) );
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

		// Always add the regular suggestions
		foreach ( $wgTranslateSandboxSuggestions as $titleText ) {
			$title = Title::newFromText( $titleText );
			if ( !$title ) {
				wfWarn( "Invalid title in \$wgTranslateSandboxSuggestions: $titleText" );
				continue;
			}

			$index = $title->getNamespace() . ':' . $handle->getKey();
			// This index might already exist, but that is okay
			$list[$index] = '';
		}

		// Message index of all known messages
		$mi = MessageIndex::singleton();
		// Get some random keys
		$all = array_keys( $mi->retrieve() );
		// In case there aren't any messages
		if ( $all === array() ) {
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
			list( $ns, $page ) = explode( ':', $index, 2 );
			$title = Title::makeTitle( $ns, "$page/{$this->language}" );
			$handle = new MessageHandle( $title );

			if ( MessageGroups::isTranslatableMessage( $handle ) ) {
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
				// or messages given in $wgTranslateSandboxSuggestions or just dated
				// message index.
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

	public function getChecker() {
		return null;
	}

	/**
	 * Subpage language code, if any in the title, is ignored.
	 * @param MessageHandle $handle
	 * @return null|string
	 */
	public function getMessageContent( MessageHandle $handle ) {
		$groupId = MessageIndex::getPrimaryGroupId( $handle );
		$group = MessageGroups::getGroup( $groupId );
		$key = $handle->getKey();

		$source = $group->getMessage( $key, $group->getSourceLanguage() );
		if ( $source !== null ) {
			return $source;
		}

		// Try harder
		if ( method_exists( $group, 'getKeys' ) ) {
			$keys = $group->getKeys();
		} else {
			$keys = array_keys( $group->getDefinitions() );
		}
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
