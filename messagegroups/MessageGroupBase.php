<?php
/**
 * This file contains a base implementation of managed message groups.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\CombinedInsertablesSuggester;
use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\InsertableFactory;
use MediaWiki\Extension\Translate\Validation\ValidationRunner;
use MediaWiki\MediaWikiServices;

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
	/** @var StringMatcher */
	protected $mangler;

	protected function __construct() {
	}

	/**
	 * @param array $conf
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

		return $conf ?? 'en';
	}

	public function getDefinitions() {
		$defs = $this->load( $this->getSourceLanguage() );

		return $defs;
	}

	protected function getFromConf( $section, $key = null ) {
		if ( $key === null ) {
			return $this->conf[$section] ?? null;
		}
		return $this->conf[$section][$key] ?? null;
	}

	public function getValidator() {
		$validatorConfigs = $this->getFromConf( 'VALIDATORS' );
		if ( $validatorConfigs === null ) {
			return null;
		}

		$msgValidator = new ValidationRunner( $this->getId() );

		foreach ( $validatorConfigs as $config ) {
			try {
				$msgValidator->addValidator( $config );
			} catch ( Exception $e ) {
				$id = $this->getId();
				throw new InvalidArgumentException(
					"Unable to construct validator for message group $id: " . $e->getMessage(),
					0,
					$e
				);
			}
		}

		return $msgValidator;
	}

	public function getMangler() {
		if ( !isset( $this->mangler ) ) {
			$class = $this->getFromConf( 'MANGLER', 'class' ) ?? StringMatcher::class;

			if ( $class === 'StringMatcher' || $class === StringMatcher::class ) {
				$this->mangler = new StringMatcher();
				$manglerConfig = $this->conf['MANGLER'] ?? null;
				if ( $manglerConfig ) {
					$this->mangler->setConf( $manglerConfig );
				}

				return $this->mangler;
			}

			throw new InvalidArgumentException(
				'Unable to create StringMangler for group ' . $this->getId() . ': ' .
				"Custom StringManglers ($class) are currently not supported."
			);
		}

		return $this->mangler;
	}

	/**
	 * Returns the configured InsertablesSuggester if any.
	 * @since 2013.09
	 * @return CombinedInsertablesSuggester
	 */
	public function getInsertablesSuggester() {
		$suggesters = [];
		$insertableConf = $this->getFromConf( 'INSERTABLES' ) ?? [];

		foreach ( $insertableConf as $config ) {
			if ( !isset( $config['class'] ) ) {
				throw new InvalidArgumentException(
					'Insertable configuration for group: ' . $this->getId() .
					' does not provide a class.'
				);
			}

			if ( !is_string( $config['class'] ) ) {
				throw new InvalidArgumentException(
					'Expected Insertable class to be string, got: ' . gettype( $config['class'] ) .
					' for group: ' . $this->getId()
				);
			}

			$suggesters[] = InsertableFactory::make( $config['class'], $config['params'] ?? [] );
		}

		// Get validators marked as insertable
		$messageValidator = $this->getValidator();
		if ( $messageValidator ) {
			$suggesters = array_merge( $suggesters, $messageValidator->getInsertableValidators() );
		}

		return new CombinedInsertablesSuggester( $suggesters );
	}

	/** @inheritDoc */
	public function getKeys() {
		return array_keys( $this->getDefinitions() );
	}

	public function getTags( $type = null ) {
		if ( $type === null ) {
			$taglist = [];

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

		$matches = [];

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
				if ( $mangler->matches( $key ) ) {
					$matches[] = $key;
				}
			}
		}

		return $matches;
	}

	protected function getRawTags( $type = null ) {
		if ( !isset( $this->conf['TAGS'] ) ) {
			return [];
		}

		$tags = $this->conf['TAGS'];
		if ( !$type ) {
			return $tags;
		}

		return $tags[$type] ?? [];
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

		$index = MediaWikiServices::getInstance()->getContentLanguage()
			->getNsIndex( $ns );

		if ( !$index ) {
			throw new MWException( "No valid namespace defined, got $ns." );
		}

		return $index;
	}

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
		$groupConfiguration = $this->getConfiguration();
		if ( !isset( $groupConfiguration['LANGUAGES'] ) ) {
			// No LANGUAGES section in the configuration.
			return null;
		}

		$codes = array_flip( array_keys( TranslateUtils::getLanguageNames( null ) ) );

		$lists = $groupConfiguration['LANGUAGES'];
		$exclusionList = $lists['exclude'] ?? null;
		if ( $exclusionList !== null ) {
			if ( $exclusionList === '*' ) {
				// All excluded languages
				$codes = [];
			} elseif ( is_array( $exclusionList ) ) {
				foreach ( $exclusionList as $code ) {
					unset( $codes[$code] );
				}
			}
		} else {
			// Treat lack of explicit exclusion list the same as excluding everything. This way,
			// when one defines only inclusions, it means that only those languages are allowed.
			$codes = [];
		}

		$disabledLanguages = Services::getInstance()->getConfigHelper()->getDisabledTargetLanguages();
		// DWIM with $wgTranslateDisabledTargetLanguages, e.g. languages in that list should not unexpectedly
		// be enabled when an inclusion list is used to include any language.
		$checks = [ $this->getId(), strtok( $this->getId(), '-' ), '*' ];
		foreach ( $checks as $check ) {
			if ( isset( $disabledLanguages[ $check ] ) ) {
				foreach ( array_keys( $disabledLanguages[ $check ] ) as $excludedCode ) {
					unset( $codes[ $excludedCode ] );
				}
			}
		}

		$inclusionList = $lists['include'] ?? null;
		if ( $inclusionList !== null ) {
			if ( $inclusionList === '*' ) {
				// All languages included (except $wgTranslateDisabledTargetLanguages)
				return null;
			} elseif ( is_array( $inclusionList ) ) {
				foreach ( $inclusionList as $code ) {
					$codes[$code] = true;
				}
			}
		}

		return $codes;
	}

	public function getSupportConfig(): ?array {
		return $this->getFromConf( 'BASIC', 'support' );
	}
}
