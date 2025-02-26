<?php
declare( strict_types = 1 );

use MediaWiki\Context\IContextSource;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupStates;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\MessageProcessing\StringMatcher;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\CombinedInsertablesSuggester;
use MediaWiki\Extension\Translate\TranslatorInterface\Insertable\InsertableFactory;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Extension\Translate\Validation\ValidationRunner;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Linker\LinkTarget;
use MediaWiki\MediaWikiServices;

/**
 * This class implements some basic functions that wrap around the YAML
 * message group configurations. These message groups use the file format classes
 * and are managed with Special:ManageMessageGroups and
 * importExternalTranslations.php.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @see https://www.mediawiki.org/wiki/Help:Extension:Translate/Group_configuration
 * @ingroup MessageGroup
 */
abstract class MessageGroupBase implements MessageGroup {
	protected array $conf;
	protected int $namespace;
	protected ?StringMatcher $mangler = null;

	protected function __construct() {
	}

	public static function factory( array $conf ): MessageGroup {
		/** @var MessageGroupBase $obj */
		$obj = new $conf['BASIC']['class']();
		$obj->conf = $conf;
		$obj->namespace = $obj->parseNamespace();

		return $obj;
	}

	public function getConfiguration(): array {
		return $this->conf;
	}

	/** @inheritDoc */
	public function getId() {
		return $this->conf['BASIC']['id'] ?? null;
	}

	/** @inheritDoc */
	public function getLabel( ?IContextSource $context = null ) {
		return $this->conf['BASIC']['label'] ?? null;
	}

	/** @inheritDoc */
	public function getDescription( ?IContextSource $context = null ) {
		return $this->conf['BASIC']['description'] ?? null;
	}

	/** @inheritDoc */
	public function getIcon() {
		return $this->conf['BASIC']['icon'] ?? null;
	}

	/** @inheritDoc */
	public function getNamespace() {
		return $this->namespace;
	}

	/** @inheritDoc */
	public function isMeta() {
		return $this->conf['BASIC']['meta'] ?? null;
	}

	/** @inheritDoc */
	public function getDefinitions() {
		return $this->load( $this->getSourceLanguage() );
	}

	/** @inheritDoc */
	public function getValidator() {
		$validatorConfigs = $this->conf['VALIDATORS'] ?? [];
		if ( !$validatorConfigs ) {
			return null;
		}

		$msgValidator = new ValidationRunner( $this->getId() );

		foreach ( $validatorConfigs as $config ) {
			try {
				$msgValidator->addValidator( $config );
			} catch ( InvalidArgumentException $e ) {
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

	/** @inheritDoc */
	public function getMangler() {
		if ( $this->mangler === null ) {
			$class = $this->conf['MANGLER']['class'] ?? StringMatcher::class;

			if ( $class === 'StringMatcher' || $class === StringMatcher::class ) {
				$this->mangler = new StringMatcher();
				$manglerConfig = $this->conf['MANGLER'] ?? null;
				if ( $manglerConfig ) {
					$this->mangler->setConf( $manglerConfig );
				}
			} else {
				throw new InvalidArgumentException(
					"Unable to create StringMangler for group {$this->getId()}: " .
					"Custom StringManglers ($class) are currently not supported."
				);
			}
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
		$insertableConf = $this->conf['INSERTABLES'] ?? [];

		foreach ( $insertableConf as $config ) {
			if ( !isset( $config['class'] ) ) {
				throw new InvalidArgumentException(
					'Insertable configuration for group: ' . $this->getId() .
					' does not provide a class.'
				);
			}

			if ( !is_string( $config['class'] ) ) {
				throw new InvalidArgumentException(
					'Expected Insertable class to be string, got: ' . get_debug_type( $config['class'] ) .
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

	/** @inheritDoc */
	public function getTags( $type = null ) {
		if ( $type === null ) {
			return array_map(
				fn ( $patterns ) => $this->parseTags( $patterns ),
				$this->getRawTags()
			);
		} else {
			return $this->parseTags( $this->getRawTags( $type ) );
		}
	}

	protected function parseTags( array $patterns ): array {
		$messageKeys = $this->getKeys();

		$matches = [];

		// Collect exact keys, no point running them through string matcher
		foreach ( $patterns as $index => $pattern ) {
			if ( !str_contains( $pattern, '*' ) ) {
				$matches[] = $pattern;
				unset( $patterns[$index] );
			}
		}

		if ( count( $patterns ) ) {
			// Rest of the keys contain wildcards.
			$mangler = new StringMatcher( '', $patterns );

			// Use mangler to find messages that match.
			foreach ( $messageKeys as $key ) {
				if ( $mangler->matches( $key ) ) {
					$matches[] = $key;
				}
			}
		}

		return $matches;
	}

	protected function getRawTags( ?string $type = null ): array {
		$tags = $this->conf['TAGS'] ?? [];
		if ( !$type ) {
			return $tags;
		}

		return $tags[$type] ?? [];
	}

	protected function setTags( MessageCollection $collection ): void {
		foreach ( $this->getTags() as $type => $tags ) {
			$collection->setTags( $type, $tags );
		}
	}

	protected function parseNamespace(): int {
		$ns = $this->conf['BASIC']['namespace'] ?? null;

		if ( is_int( $ns ) ) {
			return $ns;
		}

		if ( defined( $ns ) ) {
			return constant( $ns );
		}

		$index = MediaWikiServices::getInstance()->getContentLanguage()
			->getNsIndex( $ns );

		if ( $index === false ) {
			throw new RuntimeException( "No valid namespace defined, got $ns." );
		}

		return $index;
	}

	protected function isSourceLanguage( string $code ): bool {
		return $code === $this->getSourceLanguage();
	}

	/** @inheritDoc */
	public function getMessageGroupStates(): MessageGroupStates {
		global $wgTranslateWorkflowStates;
		$conf = $wgTranslateWorkflowStates ?: [];

		Services::getInstance()->getHookRunner()
			->onTranslate_modifyMessageGroupStates( $this->getId(), $conf );

		return new MessageGroupStates( $conf );
	}

	/** @inheritDoc */
	public function getTranslatableLanguages() {
		$languageConfig = $this->conf['LANGUAGES'] ?? null;
		if ( $languageConfig === null ) {
			// No LANGUAGES section in the configuration.
			return self::DEFAULT_LANGUAGES;
		}

		$codes = array_flip( array_keys( Utilities::getLanguageNames( LanguageNameUtils::AUTONYMS ) ) );

		$exclusionList = $languageConfig['exclude'] ?? null;
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
			if ( isset( $disabledLanguages[$check] ) ) {
				foreach ( array_keys( $disabledLanguages[$check] ) as $excludedCode ) {
					unset( $codes[$excludedCode] );
				}
			}
		}

		$inclusionList = $languageConfig['include'] ?? null;
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

	/** @inheritDoc */
	public function getSupportConfig(): ?array {
		return $this->conf['BASIC']['support'] ?? null;
	}

	/** @inheritDoc */
	public function getRelatedPage(): ?LinkTarget {
		return null;
	}
}
