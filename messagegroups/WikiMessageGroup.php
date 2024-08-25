<?php
/**
 * This file contains an unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Utilities\Utilities;

/**
 * Group for messages that can be controlled via a page in %MediaWiki namespace.
 *
 * In the page comments start with # and continue till the end of the line.
 * The page should contain list of page names in %MediaWiki namespace, without
 * the namespace prefix. Use underscores for spaces in page names, since
 * whitespace separates the page names from each other.
 * @ingroup MessageGroup
 */
class WikiMessageGroup extends MessageGroupOld {
	/** @var string */
	protected $source;

	/**
	 * @param string $id Unique id for this group.
	 * @param string $source MediaWiki message that contains list of message keys.
	 */
	public function __construct( $id, $source ) {
		$this->id = $id;
		$this->source = $source;
	}

	/**
	 * Defaults to wiki content language.
	 * @return string Language code
	 */
	public function getSourceLanguage() {
		global $wgLanguageCode;

		return $wgLanguageCode;
	}

	/**
	 * Fetch definitions from database.
	 * @return array Array of messages keys with definitions.
	 */
	public function getDefinitions() {
		$definitions = [];

		// In theory the page could have templates that are substitued
		$source = wfMessage( $this->source );
		if ( $source->isDisabled() ) {
			return [];
		}

		$contents = $source->text();
		$contents = preg_replace( '~^\s*#.*$~m', '', $contents );
		$messages = preg_split( '/\s+/', $contents );

		foreach ( $messages as $message ) {
			if ( !$message ) {
				continue;
			}

			$definitions[$message] = wfMessage( $message )->inContentLanguage()->plain();
		}

		return $definitions;
	}

	/**
	 * Returns of stored translation of message specified by the $key in language
	 * code $code.
	 *
	 * @param string $key Key of the message.
	 * @param string $code Language code.
	 * @return string|null The translation or null if it doesn't exist.
	 */
	public function getMessage( $key, $code ) {
		if ( $code && $this->getSourceLanguage() !== $code ) {
			return Utilities::getMessageContent( $key, $code );
		} else {
			return Utilities::getMessageContent( $key, '' );
		}
	}
}
