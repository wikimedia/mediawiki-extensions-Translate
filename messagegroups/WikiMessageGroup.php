<?php
/**
 * This file contains a unmanaged message group implementation.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2012, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

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
	protected $source = null;

	/**
	 * Constructor.
	 *
	 * @param $id \string Unique id for this group.
	 * @param $source \string Mediawiki message that contains list of message keys.
	 */
	public function __construct( $id, $source ) {
		parent::__construct();
		$this->id = $id;
		$this->source = $source;
	}

	/// Defaults to wiki content language.
	public function getSourceLanguage() {
		global $wgLanguageCode;

		return $wgLanguageCode;
	}

	/**
	 * Fetch definitions from database.
	 * @return \array Array of messages keys with definitions.
	 */
	public function getDefinitions() {
		$definitions = array();

		// In theory the page could have templates that are substitued
		$contents = wfMessage( $this->source )->text();
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
	 * @param $key \string Key of the message.
	 * @param $code \string Language code.
	 * @return \types{\string,\null} The translation or null if it doesn't exists.
	 */
	public function getMessage( $key, $code ) {
		if ( $code && $this->getSourceLanguage() !== $code ) {
			return TranslateUtils::getMessageContent( $key, $code );
		} else {
			return TranslateUtils::getMessageContent( $key, false );
		}
	}
}
