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
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;
use MediaWiki\Extension\Translate\TranslatorInterface\Aid\TranslationAid;

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
	 * @param IContextSource|null $context
	 * @return string
	 */
	public function getLabel( IContextSource $context = null ) {
		return $this->label;
	}

	/** @param string $value */
	public function setLabel( $value ) {
		$this->label = $value;
	}

	/**
	 * Group-wide unique id of this group. Used also for sorting.
	 */
	protected $id = 'none';

	/** @return string */
	public function getId() {
		return $this->id;
	}

	/** @param string $value */
	public function setId( $value ) {
		$this->id = $value;
	}

	/**
	 * The namespace where all the messages of this group belong.
	 * If the group has messages from multiple namespaces, set this to false
	 * and look how RecentMessageGroup implements the definitions.
	 */
	protected $namespace = NS_MEDIAWIKI;

	/**
	 * Get the namespace where all the messages of this group belong.
	 * @return int
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * Set the namespace where all the messages of this group belong.
	 * @param int $ns
	 */
	public function setNamespace( $ns ) {
		$this->namespace = $ns;
	}

	/**
	 * Holds description of this group. Description is a wiki text snippet that
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

	public function getSourceLanguage() {
		return 'en';
	}

	/**
	 * To avoid key conflicts between groups or separated changed messages between
	 * branches one can set a message key mangler.
	 */
	protected $mangler = null;

	/** @return StringMatcher */
	public function getMangler() {
		if ( !isset( $this->mangler ) ) {
			$this->mangler = new StringMatcher();
		}

		return $this->mangler;
	}

	public function load( $code ) {
		return [];
	}

	/**
	 * This function returns array of type key => definition of all messages
	 * this message group handles.
	 *
	 * @throws MWException
	 * @return string[] List of message definitions indexed by key.
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
		return $this->meta ? [] : $this->getDefinitions();
	}

	/** @inheritDoc */
	public function getKeys() {
		return array_keys( $this->getDefinitions() );
	}

	/**
	 * Returns of stored translation of message specified by the $key in language
	 * code $code.
	 *
	 * @param string $key Message key
	 * @param string $code Language code
	 * @return mixed List of stored translation or \null.
	 */
	public function getMessage( $key, $code ) {
		if ( !isset( $this->messages[$code] ) ) {
			$this->messages[$code] = self::normaliseKeys( $this->load( $code ) );
		}
		$key = strtolower( str_replace( ' ', '_', $key ) );

		return $this->messages[$code][$key] ?? null;
	}

	public static function normaliseKeys( $array ) {
		if ( !is_array( $array ) ) {
			return null;
		}

		$new = [];
		foreach ( $array as $key => $v ) {
			$key = strtolower( str_replace( ' ', '_', $key ) );
			$new[$key] = $v;
		}

		return $new;
	}

	/**
	 * All the messages for this group, by language code.
	 */
	protected $messages = [];

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
	 * @param string $code
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
	 * @param string $code Language code for this collection.
	 * @param bool $unique Whether to build collection for messages unique to this
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

	/**
	 * Can be overwritten to return false if something is wrong.
	 * @return bool
	 */
	public function exists() {
		return true;
	}

	public function getValidator() {
		return null;
	}

	public function getTags( $type = null ) {
		return [];
	}

	/**
	 * @param string $code
	 * @return bool
	 */
	protected function isSourceLanguage( $code ) {
		return $code === $this->getSourceLanguage();
	}

	/**
	 * Get the message group workflow state configuration.
	 * @return MessageGroupStates
	 */
	public function getMessageGroupStates() {
		global $wgTranslateWorkflowStates;
		$conf = $wgTranslateWorkflowStates ?: [];

		Hooks::run( 'Translate:modifyMessageGroupStates', [ $this->getId(), &$conf ] );

		return new MessageGroupStates( $conf );
	}

	/** @inheritDoc */
	public function getTranslatableLanguages() {
		return null;
	}

	protected static function addContext( Message $message, IContextSource $context = null ) {
		if ( $context ) {
			$message->inLanguage( $context->getLanguage() );
		} else {
			$message->inLanguage( 'en' );
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

	public function getSupportConfig(): ?array {
		return null;
	}
}
