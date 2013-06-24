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
		global $wgTranslateSandboxSuggestions;

		$list = array();

		$mi = MessageIndex::singleton();
		foreach ( $wgTranslateSandboxSuggestions as $titleText ) {
			$title = Title::newFromText( $titleText );
			if ( !$title ) {
				wfWarn( "Invalid title in \$wgTranslateSandboxSuggestions: $titleText" );
				continue;
			}

			$handle = new MessageHandle( $title );
			if ( $mi->getGroupIds( $handle ) === array() ) {
				wfWarn( "Title does not belong to any group in \$wgTranslateSandboxSuggestions: $titleText" );
				continue;
			}

			$index = $title->getNamespace() . ':' . $handle->getKey();
			$list[$index] = '';
		}

		// Get some random ones
		$all = array_keys( $mi->retrieve() );
		// In case there aren't any messages
		if ( $all === array() ) {
			return array();
		}
		$min = 0;
		$max = count( $all ) - 1; // Indexes are zero-based

		// Get some message. Will be filtered to less below.
		for ( $i = count( $list ); $i < 100; $i++ ) {
			$list[$all[rand( $min, $max )]] = '';
		}

		// Ugly
		$store = new TranslationStashStorage( wfGetDB( DB_MASTER ) );
		$user = RequestContext::getMain()->getUser();
		$translations = $store->getTranslations( $user );

		// Filter out the ones the user has already translated
		foreach ( array_keys( $translations ) as $titleText ) {
			$title = Title::newFromText( $titleText );
			$handle = new MessageHandle( $title );
			$index = $title->getNamespace() . ':' . $handle->getKey();
			unset( $list[$index] );
		}

		// Fetch definitions, slowly, one by one
		$count = 0;
		foreach ( $list as $index => &$translation ) {
			list( $ns, $page ) = explode( ':', $index, 2 );
			$title = Title::makeTitle( $ns, "$page/{$this->language}" );
			$handle = new MessageHandle( $title );

			if ( MessageGroups::isTranslatableMessage( $handle ) ) {
				$count++;
				$translation = $this->getMessageContent( $handle );
			} else {
				unset( $list[$index] );
			}

			// Only fetch 20 messages at once
			if ( $count === 20 ) {
				break;
			}
		}

		// Remove the extra entries
		$list = array_slice( $list, 0, 20 );

		return $list;
	}

	public function getChecker() {
		return null;
	}

	/**
	 * Subpage language code, if any in the title, is ignored.
	 */
	public function getMessageContent( MessageHandle $handle ) {
		$groupId = MessageIndex::getPrimaryGroupId( $handle );
		$group = MessageGroups::getGroup( $groupId );
		if ( $group ) {
			return $group->getMessage( $handle->getKey(), $group->getSourceLanguage() );
		}

		throw new MWException( 'Could not find group for ' . $handle->getKey() );
	}
}
