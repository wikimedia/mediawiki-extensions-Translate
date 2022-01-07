<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate;

use MediaWiki\Extension\Translate\Cache\PersistentCache;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageMover;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageParser;
use MediaWiki\Extension\Translate\PageTranslation\TranslationUnitStoreFactory;
use MediaWiki\Extension\Translate\Statistics\ProgressStatsTableFactory;
use MediaWiki\Extension\Translate\Statistics\TranslationStatsDataProvider;
use MediaWiki\Extension\Translate\Statistics\TranslatorActivity;
use MediaWiki\Extension\Translate\Synchronization\ExternalMessageSourceStateImporter;
use MediaWiki\Extension\Translate\Synchronization\GroupSynchronizationCache;
use MediaWiki\Extension\Translate\TranslatorInterface\EntitySearch;
use MediaWiki\Extension\Translate\TranslatorSandbox\TranslationStashReader;
use MediaWiki\Extension\Translate\TtmServer\TtmServerFactory;
use MediaWiki\Extension\Translate\Utilities\ConfigHelper;
use MediaWiki\Extension\Translate\Utilities\ParsingPlaceholderFactory;
use MediaWiki\MediaWikiServices;
use MessageIndex;
use Psr\Container\ContainerInterface;

/**
 * Minimal service container.
 *
 * Main purpose is to give type-hinted getters for services defined in this extension.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.04
 */
class Services implements ContainerInterface {
	/** @var ContainerInterface */
	private $container;

	private function __construct( ContainerInterface $container ) {
		$this->container = $container;
	}

	public static function getInstance(): Services {
		return new self( MediaWikiServices::getInstance() );
	}

	/** @inheritDoc */
	public function get( $id ) {
		return $this->container->get( $id );
	}

	/** @inheritDoc */
	public function has( $id ) {
		return $this->container->has( $id );
	}

	public function getConfigHelper(): ConfigHelper {
		return $this->get( 'Translate:ConfigHelper' );
	}

	/** @since 2021.10 */
	public function getEntitySearch(): EntitySearch {
		return $this->get( 'Translate:EntitySearch' );
	}

	public function getExternalMessageSourceStateImporter(): ExternalMessageSourceStateImporter {
		return $this->get( 'Translate:ExternalMessageSourceStateImporter' );
	}

	public function getGroupSynchronizationCache(): GroupSynchronizationCache {
		return $this->get( 'Translate:GroupSynchronizationCache' );
	}

	/** @since 2020.10 */
	public function getMessageIndex(): MessageIndex {
		return $this->get( 'Translate:MessageIndex' );
	}

	/** @since 2020.07 */
	public function getParsingPlaceholderFactory(): ParsingPlaceholderFactory {
		return $this->get( 'Translate:ParsingPlaceholderFactory' );
	}

	/** @since 2020.12 */
	public function getPersistentCache(): PersistentCache {
		return $this->get( 'Translate:PersistentCache' );
	}

	/** @since 2020.12 */
	public function getProgressStatsTableFactory(): ProgressStatsTableFactory {
		return $this->get( 'Translate:ProgressStatsTableFactory' );
	}

	/** @since 2021.03 */
	public function getTranslatablePageMover(): TranslatablePageMover {
		return $this->get( 'Translate:TranslatablePageMover' );
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

	/** @since 2021.05 */
	public function getTranslationUnitStoreFactory(): TranslationUnitStoreFactory {
		return $this->get( 'Translate:TranslationUnitStoreFactory' );
	}

	public function getTranslatorActivity(): TranslatorActivity {
		return $this->get( 'Translate:TranslatorActivity' );
	}

	/** @since 2021.01 */
	public function getTtmServerFactory(): TtmServerFactory {
		return $this->get( 'Translate:TtmServerFactory' );
	}
}
