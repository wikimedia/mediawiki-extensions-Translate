<?php
/**
 * This file contains the base information of unmanaged message groups.
 * These classes don't use Yaml configuration nor Special:ManageMessageGroups
 * nor processMessageChanges.php
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2013, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * This is the interface and base implementation of unmanaged
 * message groups.
 * @todo Rename the class
 * @ingroup MessageGroup
 */
abstract class MessageGroupOld implements MessageGroup {
	/**
	 * Human-readable name of this group
	 */
	protected $label = 'none';

	/**
	 * @param IContextSource $context
	 * @return string
	 */
	public function getLabel( IContextSource $context = null ) {
		return $this->label;
	}

	/**
	 * @param $value string
	 */
	public function setLabel( $value ) {
		$this->label = $value;
	}

	/**
	 * Group-wide unique id of this group. Used also for sorting.
	 */
	protected $id = 'none';

	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param $value string
	 */
	public function setId( $value ) {
		$this->id = $value;
	}

	/**
	 * The namespace where all the messages of this group belong.
	 * If the group has messages from multiple namespaces, set this to false
	 * and look how RecentMessageGroup implements the definitions.
	 */
	protected $namespace = NS_MEDIAWIKI;

	/// Get the namespace where all the messages of this group belong.
	public function getNamespace() {
		return $this->namespace;
	}

	/// Set the namespace where all the messages of this group belong.
	public function setNamespace( $ns ) {
		$this->namespace = $ns;
	}

	/**
	 * List of messages that are hidden by default, but can still be translated if
	 * needed.
	 */
	protected $optional = array();

	/**
	 * @return array
	 */
	public function getOptional() {
		return $this->optional;
	}

	/**
	 * @param $value array
	 */
	public function setOptional( $value ) {
		$this->optional = $value;
	}

	/**
	 * List of messages that are always hidden and cannot be translated.
	 */
	protected $ignored = array();

	/**
	 * @return array
	 */
	public function getIgnored() {
		return $this->ignored;
	}

	/**
	 * @param $value array
	 */
	public function setIgnored( $value ) {
		$this->ignored = $value;
	}

	/**
	 * Holds descripton of this group. Description is a wiki text snippet that
	 * gives information about this group to translators.
	 */
	protected $description = null;

	public function getDescription( IContextSource $context = null ) {
		return $this->description;
	}

	public function setDescription( $value ) {
		$this->description = $value;
	}

	public function getIcon() {
		return null;
	}

	/**
	 * Meta groups consist of multiple groups or parts of other groups. This info
	 * is used on many places, like when creating message index.
	 */
	protected $meta = false;

	public function isMeta() {
		return $this->meta;
	}

	public function setMeta( $value ) {
		$this->meta = $value;
	}

	public function getSourceLanguage() {
		return 'en';
	}

	/**
	 * To avoid key conflicts between groups or separated changed messages between
	 * branches one can set a message key mangler.
	 */
	protected $mangler = null;

	/**
	 * @return StringMatcher
	 */
	public function getMangler() {
		if ( !isset( $this->mangler ) ) {
			$this->mangler = StringMatcher::EmptyMatcher();
		}

		return $this->mangler;
	}

	public function setMangler( $value ) {
		$this->mangler = $value;
	}

	public function load( $code ) {
		return array();
	}

	/**
	 * This function returns array of type key => definition of all messages
	 * this message group handles.
	 *
	 * @throws MWException
	 * @return Array of messages definitions indexed by key.
	 */
	public function getDefinitions() {
		$defs = $this->load( $this->getSourceLanguage() );
		if ( !is_array( $defs ) ) {
			throw new MWException( 'Unable to load definitions for ' . $this->getLabel() );
		}

		return $defs;
	}

	/**
	 * This function can be used for meta message groups to list their "own"
	 * messages. For example branched message groups can exclude the messages they
	 * share with each other.
	 * @return array
	 */
	public function getUniqueDefinitions() {
		return $this->meta ? array() : $this->getDefinitions();
	}

	/**
	 * Returns of stored translation of message specified by the $key in language
	 * code $code.
	 *
	 * @param string $key Message key
	 * @param string $code Language code
	 * @return Mixed List of stored translation or \null.
	 */
	public function getMessage( $key, $code ) {
		if ( !isset( $this->messages[$code] ) ) {
			$this->messages[$code] = self::normaliseKeys( $this->load( $code ) );
		}
		$key = strtolower( str_replace( ' ', '_', $key ) );

		return isset( $this->messages[$code][$key] ) ? $this->messages[$code][$key] : null;
	}

	public static function normaliseKeys( $array ) {
		if ( !is_array( $array ) ) {
			return null;
		}

		$new = array();
		foreach ( $array as $key => $v ) {
			$key = strtolower( str_replace( ' ', '_', $key ) );
			$new[$key] = $v;
		}

		return $new;
	}

	/**
	 * All the messages for this group, by language code.
	 */
	protected $messages = array();

	/**
	 * Returns path to the file where translation of language code $code are.
	 *
	 * @param string $code
	 * @return string Path to the file or false if not applicable.
	 */
	public function getMessageFile( $code ) {
		return false;
	}

	public function getPath() {
		return false;
	}

	/**
	 * @param $code
	 * @return bool|string
	 */
	public function getMessageFileWithPath( $code ) {
		$path = $this->getPath();
		$file = $this->getMessageFile( $code );

		if ( !$path || !$file ) {
			return false;
		}

		return "$path/$file";
	}

	public function getSourceFilePath( $code ) {
		return $this->getMessageFileWithPath( $code );
	}

	/**
	 * Creates a new MessageCollection for this group.
	 *
	 * @param $code \string Language code for this collection.
	 * @param $unique \bool Whether to build collection for messages unique to this
	 *                group only.
	 * @return MessageCollection
	 */
	public function initCollection( $code, $unique = false ) {
		if ( !$unique ) {
			$definitions = $this->getDefinitions();
		} else {
			$definitions = $this->getUniqueDefinitions();
		}

		$defs = new MessageDefinitions( $definitions, $this->getNamespace() );
		$collection = MessageCollection::newFromDefinitions( $defs, $code );

		foreach ( $this->getTags() as $type => $tags ) {
			$collection->setTags( $type, $tags );
		}

		return $collection;
	}

	public function __construct() {
	}

	/**
	 * Can be overwritten to retun false if something is wrong.
	 * @return bool
	 */
	public function exists() {
		return true;
	}

	public function getChecker() {
		return null;
	}

	public function getTags( $type = null ) {
		$tags = array(
			'optional' => $this->optional,
			'ignored' => $this->ignored,
		);

		if ( !$type ) {
			return $tags;
		}

		return isset( $tags[$type] ) ? $tags[$type] : array();
	}

	/**
	 * @param $code string
	 * @return bool
	 */
	protected function isSourceLanguage( $code ) {
		return $code === $this->getSourceLanguage();
	}

	// Unsupported stuff, just to satisfy the new interface
	public function setConfiguration( $conf ) {
	}

	public function getConfiguration() {
	}

	public function getFFS() {
		return null;
	}

	/**
	 * @deprecated Use getMessageGroupStates
	 */
	public function getWorkflowConfiguration() {
		global $wgTranslateWorkflowStates;
		if ( !$wgTranslateWorkflowStates ) {
			// Not configured
			$conf = array();
		} else {
			$conf = $wgTranslateWorkflowStates;
		}

		return $conf;
	}

	/**
	 * Get the message group workflow state configuration.
	 * @return MessageGroupStates
	 */
	public function getMessageGroupStates() {
		// @todo Replace deprecated call.
		$conf = $this->getWorkflowConfiguration();

		return new MessageGroupStates( $conf );
	}

	/**
	 * Get all the translatable languages for a group, considering the whitelisting
	 * and blacklisting.
	 * @return array|null The language codes as array keys.
	 */
	public function getTranslatableLanguages() {
		return null;
	}

	protected static function addContext( Message $message, IContextSource $context = null ) {
		if ( $context ) {
			$message->inLanguage( $context->getLanguage() );
		}

		return $message;
	}

	/**
	 * List of available message types mapped to the classes
	 * implementing them. Default implementation (all).
	 *
	 * @return array
	 */
	public function getTranslationAids() {
		return TranslationAid::getTypes();
	}
}
