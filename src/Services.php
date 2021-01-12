<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */
namespace MediaWiki\Extension\Translate;

use MediaWiki\Extension\Translate\Cache\PersistentCache;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageParser;
use MediaWiki\Extension\Translate\Statistics\TranslationStatsDataProvider;
use MediaWiki\Extension\Translate\Statistics\TranslatorActivity;
use MediaWiki\Extension\Translate\Synchronization\GroupSynchronizationCache;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashReader;
use MediaWiki\Extension\Translate\TtmServer\TtmServerFactory;
use MediaWiki\Extension\Translate\Utilities\Json\JsonCodec;
use MediaWiki\Extension\Translate\Utilities\ParsingPlaceholderFactory;
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

	/** @since 2020.12 */
	public function getPersistentCache(): PersistentCache {
		return $this->get( 'Translate:PersistentCache' );
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

	/** @since 2021.01 */
	public function getTtmServerFactory(): TtmServerFactory {
		return $this->get( 'Translate:TtmServerFactory' );
	}
}

class_alias( Services::class, '\MediaWiki\Extensions\Translate\Services' );
