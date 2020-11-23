<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
namespace MediaWiki\Extensions\Translate;

use MediaWiki\Extensions\Translate\PageTranslation\TranslatablePageParser;
use MediaWiki\Extensions\Translate\Statistics\TranslationStatsDataProvider;
use MediaWiki\Extensions\Translate\Statistics\TranslatorActivity;
use MediaWiki\Extensions\Translate\Synchronization\GroupSynchronizationCache;
use MediaWiki\Extensions\Translate\TranslatorSandbox\TranslationStashReader;
use MediaWiki\Extensions\Translate\Utilities\Json\JsonCodec;
use MediaWiki\Extensions\Translate\Utilities\ParsingPlaceholderFactory;
use MediaWiki\MediaWikiServices;
use Psr\Container\ContainerInterface;

/**
 * Minimal service container.
 *
 * Main purpose is to give type-hinted getters for services defined in this extensions.
 *
 * @since 2020.04
 */
class Services implements ContainerInterface {
	/** @var self */
	private static $instance;

	/** @var ContainerInterface */
	private $container;

	private function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	public static function getInstance(): Services {
		self::$instance = self::$instance ?? new self( MediaWikiServices::getInstance() );
		return self::$instance;
	}

	/** @inheritDoc */
	public function get( $id ) {
		return $this->container->get( $id );
	}

	/** @inheritDoc */
	public function has( $id ) {
		return $this->container->has( $id );
	}

	public function getGroupSynchronizationCache(): GroupSynchronizationCache {
		return $this->get( 'Translate:GroupSynchronizationCache' );
	}

	/** @since 2020.12 */
	public function getJsonCodec(): JsonCodec {
		return $this->get( 'Translate:JsonCodec' );
	}

	/** @since 2020.07 */
	public function getParsingPlaceholderFactory(): ParsingPlaceholderFactory {
		return $this->get( 'Translate:ParsingPlaceholderFactory' );
	}

	public function getTranslatablePageParser(): TranslatablePageParser {
		return $this->get( 'Translate:TranslatablePageParser' );
	}

	/** @since 2020.11 */
	public function getTranslationStashReader(): TranslationStashReader {
		return $this->get( 'Translate:TranslationStashReader' );
	}

	/** @since 2020.09 */
	public function getTranslationStatsDataProvider(): TranslationStatsDataProvider {
		return $this->get( 'Translate:TranslationStatsDataProvider' );
	}

	public function getTranslatorActivity(): TranslatorActivity {
		return $this->get( 'Translate:TranslatorActivity' );
	}
}
