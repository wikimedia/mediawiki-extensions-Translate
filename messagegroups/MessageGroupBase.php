<?php
/**
 * This file contains a base implementation of managed message groups.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * This class implements some basic functions that wrap around the YAML
 * message group configurations. These message groups use the FFS classes
 * and are managed with Special:ManageMessageGroups and
 * processMessageChanges.php.
 *
 * @see https://www.mediawiki.org/wiki/Help:Extension:Translate/Group_configuration
 * @ingroup MessageGroup
 */
abstract class MessageGroupBase implements MessageGroup {
	protected $conf;
	protected $namespace;
	protected $groups;

	/**
	 * @var StringMatcher
	 */
	protected $mangler;

	protected function __construct() {
	}

	/**
	 * @param $conf
	 *
	 * @return MessageGroup
	 */
	public static function factory( $conf ) {
		$obj = new $conf['BASIC']['class']();
		$obj->conf = $conf;
		$obj->namespace = $obj->parseNamespace();

		return $obj;
	}

	public function getConfiguration() {
		return $this->conf;
	}

	public function getId() {
		return $this->getFromConf( 'BASIC', 'id' );
	}

	public function getLabel( IContextSource $context = null ) {
		return $this->getFromConf( 'BASIC', 'label' );
	}

	public function getDescription( IContextSource $context = null ) {
		return $this->getFromConf( 'BASIC', 'description' );
	}

	public function getIcon() {
		return $this->getFromConf( 'BASIC', 'icon' );
	}

	public function getNamespace() {
		return $this->namespace;
	}

	public function isMeta() {
		return $this->getFromConf( 'BASIC', 'meta' );
	}

	public function getSourceLanguage() {
		$conf = $this->getFromConf( 'BASIC', 'sourcelanguage' );

		return $conf !== null ? $conf : 'en';
	}

	public function getDefinitions() {
		$defs = $this->load( $this->getSourceLanguage() );

		return $defs;
	}

	protected function getFromConf( $section, $key ) {
		return isset( $this->conf[$section][$key] ) ? $this->conf[$section][$key] : null;
	}

	/**
	 * @return FFS
	 * @throws MWException
	 */
	public function getFFS() {
		$class = $this->getFromConf( 'FILES', 'class' );

		if ( $class === null ) {
			return null;
		}

		if ( !class_exists( $class ) ) {
			throw new MWException( "FFS class $class does not exist." );
		}

		return new $class( $this );
	}

	public function getChecker() {
		$class = $this->getFromConf( 'CHECKER', 'class' );

		if ( $class === null ) {
			return null;
		}

		if ( !class_exists( $class ) ) {
			throw new MWException( "Checker class $class does not exist." );
		}

		$checker = new $class( $this );
		$checks = $this->getFromConf( 'CHECKER', 'checks' );

		if ( !is_array( $checks ) ) {
			throw new MWException( "Checker class $class not supplied with proper checks." );
		}

		foreach ( $checks as $check ) {
			$checker->addCheck( array( $checker, $check ) );
		}

		return $checker;
	}

	public function getMangler() {
		if ( !isset( $this->mangler ) ) {
			$class = $this->getFromConf( 'MANGLER', 'class' );

			if ( $class === null ) {
				$this->mangler = StringMatcher::EmptyMatcher();

				return $this->mangler;
			}

			if ( !class_exists( $class ) ) {
				throw new MWException( "Mangler class $class does not exist." );
			}

			/**
			 * @todo Branch handling, merge with upper branch keys
			 */
			$this->mangler = new $class();
			$this->mangler->setConf( $this->conf['MANGLER'] );
		}

		return $this->mangler;
	}

	/**
	 * Returns the configured InsertablesSuggester if any.
	 * @since 2013.09
	 */
	public function getInsertablesSuggester() {
		$class = $this->getFromConf( 'INSERTABLES', 'class' );

		if ( !$class ) {
			return null;
		}

		if ( !class_exists( $class ) ) {
			throw new MWException( "InsertablesSuggester class $class does not exist." );
		}

		return new $class();
	}

	/**
	 * Optimized version of array_keys( $_->getDefinitions() ).
	 * @return array
	 * @since 2012-08-21
	 */
	public function getKeys() {
		$cache = new MessageGroupCache( $this, $this->getSourceLanguage() );
		if ( !$cache->exists() ) {
			return array_keys( $this->getDefinitions() );
		} else {
			return $cache->getKeys();
		}
	}

	/**
	 * @param string $code Language code.
	 * @return MessageCollection
	 */
	public function initCollection( $code ) {
		$namespace = $this->getNamespace();
		$messages = array();

		$cache = new MessageGroupCache( $this, $this->getSourceLanguage() );
		if ( !$cache->exists() ) {
			wfWarn( "By-passing message group cache for {$this->getId()}" );
			$messages = $this->getDefinitions();
		} else {
			foreach ( $cache->getKeys() as $key ) {
				$messages[$key] = $cache->get( $key );
			}
		}

		$definitions = new MessageDefinitions( $messages, $namespace );
		$collection = MessageCollection::newFromDefinitions( $definitions, $code );
		$this->setTags( $collection );

		return $collection;
	}

	/**
	 * @param string $key Message key
	 * @param string $code Language code
	 * @return string|null
	 */
	public function getMessage( $key, $code ) {
		$cache = new MessageGroupCache( $this, $code );
		if ( $cache->exists() ) {
			$msg = $cache->get( $key );

			if ( $msg !== false ) {
				return $msg;
			}

			// Try harder
			$nkey = str_replace( ' ', '_', strtolower( $key ) );
			$keys = $cache->getKeys();

			foreach ( $keys as $k ) {
				if ( $nkey === str_replace( ' ', '_', strtolower( $k ) ) ) {
					return $cache->get( $k );
				}
			}

			return null;
		} else {
			return null;
		}
	}

	public function getTags( $type = null ) {
		if ( $type === null ) {
			$taglist = array();

			foreach ( $this->getRawTags() as $type => $patterns ) {
				$taglist[$type] = $this->parseTags( $patterns );
			}

			return $taglist;
		} else {
			return $this->parseTags( $this->getRawTags( $type ) );
		}
	}

	protected function parseTags( $patterns ) {
		$messageKeys = $this->getKeys();

		$matches = array();

		/**
		 * Collect exact keys, no point running them trough string matcher
		 */
		foreach ( $patterns as $index => $pattern ) {
			if ( strpos( $pattern, '*' ) === false ) {
				$matches[] = $pattern;
				unset( $patterns[$index] );
			}
		}

		if ( count( $patterns ) ) {
			/**
			 * Rest of the keys contain wildcards.
			 */
			$mangler = new StringMatcher( '', $patterns );

			/**
			 * Use mangler to find messages that match.
			 */
			foreach ( $messageKeys as $key ) {
				if ( $mangler->match( $key ) ) {
					$matches[] = $key;
				}
			}
		}

		return $matches;
	}

	protected function getRawTags( $type = null ) {
		if ( !isset( $this->conf['TAGS'] ) ) {
			return array();
		}

		$tags = $this->conf['TAGS'];
		if ( !$type ) {
			return $tags;
		}

		if ( isset( $tags[$type] ) ) {
			return $tags[$type];
		}

		return array();
	}

	protected function setTags( MessageCollection $collection ) {
		foreach ( $this->getTags() as $type => $tags ) {
			$collection->setTags( $type, $tags );
		}
	}

	protected function parseNamespace() {
		$ns = $this->getFromConf( 'BASIC', 'namespace' );

		if ( is_int( $ns ) ) {
			return $ns;
		}

		if ( defined( $ns ) ) {
			return constant( $ns );
		}

		global $wgContLang;

		$index = $wgContLang->getNsIndex( $ns );

		if ( !$index ) {
			throw new MWException( "No valid namespace defined, got $ns." );
		}

		return $index;
	}

	protected function isSourceLanguage( $code ) {
		return $code === $this->getSourceLanguage();
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
		$groupConfiguration = $this->getConfiguration();
		if ( !isset( $groupConfiguration['LANGUAGES'] ) ) {
			// No LANGUAGES section in the configuration.
			return null;
		}

		$lists = $groupConfiguration['LANGUAGES'];
		$codes = array(); // The list of languages to return

		if ( isset( $lists['blacklist'] ) ) {
			$blacklist = $lists['blacklist'];
			if ( is_array( $blacklist ) ) {
				$codes = array_flip( array_keys( TranslateUtils::getLanguageNames( 'en' ) ) );
				foreach ( $blacklist as $code ) {
					unset( $codes[$code] );
				}
			} else {
				// All languages blacklisted. This is very rare but not impossible.
				$codes = array();
			}
		}

		$whitelist = array();
		if ( isset( $lists['whitelist'] ) ) {
			$whitelist = $lists['whitelist'];
			if ( $whitelist === '*' ) {
				// All languages whitelisted
				return null;
			}
		}

		foreach ( $whitelist as $code ) {
			$codes[$code] = true;
		}

		return $codes;
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
