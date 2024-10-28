<?php
/**
 * This file holds a message group interface.
 *
 * @file
 * @defgroup MessageGroup Message group
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupStates;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\Validation\ValidationRunner;
use MediaWiki\Linker\LinkTarget;

/**
 * Interface for message groups.
 *
 * Message groups are the heart of the Translate extension. They encapsulate
 * a set of messages each. Aside from basic information like id, label and
 * description, the class defines which mangler, validators and file
 * system support (FFS), if any, the group uses.
 *
 * @ingroup MessageGroup
 */
interface MessageGroup {
	/**
	 * Returns the unique identifier for this group.
	 * @return string
	 */
	public function getId();

	/**
	 * Returns the human readable label (as plain text).
	 * Parameter $context was added in 2012-10-22.
	 * @param IContextSource|null $context Context can be used by subclasses to provide
	 *   translated descriptions, for example.
	 * @return string
	 */
	public function getLabel( ?IContextSource $context = null );

	/**
	 * Returns a longer description about the group. Description can use wikitext.
	 * Parameter $context was added in 2012-10-22.
	 * @param IContextSource|null $context Context can be used by subclasses to provide
	 *   translated descriptions, for example.
	 * @return string
	 */
	public function getDescription( ?IContextSource $context = null );

	/**
	 * Returns an icon for this message group if any.
	 * @return string|null File reference in one of the supported protocols:
	 *  - file://Filename.ext - Accessible via MediaWiki functions
	 * @since 2012-12-04
	 */
	public function getIcon();

	/**
	 * Returns the namespace where messages are placed.
	 * @return int
	 */
	public function getNamespace();

	/**
	 * @todo Unclear usage. Perhaps rename to isSecondary with the only purpose
	 *       suppress warnings about message key conflicts.
	 * @return bool
	 */
	public function isMeta();

	/**
	 * If this function returns false, the message group is ignored and treated
	 * like it would not be configured at all. Useful for graceful degradation.
	 * Try to keep the check fast to avoid performance problems.
	 * @return bool
	 */
	public function exists();

	/**
	 * Returns a message validator object or null.
	 * @return ValidationRunner|null
	 */
	public function getValidator();

	/**
	 * Return a message mangler or null.
	 * @todo Make an interface for message manglers
	 * @return \MediaWiki\Extension\Translate\MessageProcessing\StringMatcher|null
	 */
	public function getMangler();

	/**
	 * Initialises a message collection with the given language code,
	 * message definitions and message tags.
	 * @param string $code
	 * @return MessageCollection
	 */
	public function initCollection( $code );

	/**
	 * Returns a list of messages in a given language code. For some groups
	 * that list may be identical with the translation in the wiki. For other
	 * groups the messages may be loaded from a file (and differ from the
	 * current translations or definitions).
	 * @param string $code
	 * @return string[]
	 */
	public function load( $code );

	/**
	 * Shortcut for load( getSourceLanguage() ).
	 * @return string[]
	 */
	public function getDefinitions();

	/**
	 * Shortcut for array_keys( getDefinitions() ) that can be optimized by
	 * the implementing classes.
	 * @return string[] List of message keys.
	 */
	public function getKeys();

	/**
	 * Returns message tags. If type is given, only message keys with that
	 * tag are returned. Otherwise an array[tag => keys] is returned.
	 * @param string|null $type
	 * @return array
	 */
	public function getTags( $type = null );

	/**
	 * Returns the definition or translation for given message key in given
	 * language code.
	 * @param string $key Message key
	 * @param string $code Language code
	 * @return string|null
	 */
	public function getMessage( $key, $code );

	/**
	 * Returns language code depicting the language of source text.
	 * @return string
	 */
	public function getSourceLanguage();

	/**
	 * Get the message group workflow state configuration.
	 * @return MessageGroupStates
	 */
	public function getMessageGroupStates();

	/**
	 * Get all the translatable languages for a group, considering the inclusion
	 * and exclusion list.
	 * @return array|null The language codes as array keys.
	 */
	public function getTranslatableLanguages();

	/**
	 * Gets support URL defined for the group if any
	 *
	 * @return array|null
	 */
	public function getSupportConfig(): ?array;

	/** Returns the page where the messages in the group are defined. */
	public function getRelatedPage(): ?LinkTarget;
}
