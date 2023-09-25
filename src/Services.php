<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate;

use MediaWiki\Extension\Translate\Cache\PersistentCache;
use MediaWiki\Extension\Translate\FileFormatSupport\FileFormatFactory;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundleStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\CsvTranslationImporter;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupReviewStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleExporter;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleFactory;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleImporter;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleStatusStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatablePageStore;
use MediaWiki\Extension\Translate\PageTranslation\TranslatableBundleMover;
use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageParser;
use MediaWiki\Extension\Translate\PageTranslation\TranslationUnitStoreFactory;
use MediaWiki\Extension\Translate\Statistics\MessageGroupStatsTableFactory;
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
	public function get( string $id ) {
		return $this->container->get( $id );
	}

	/** @inheritDoc */
	public function has( string $id ): bool {
		return $this->container->has( $id );
	}

	public function getConfigHelper(): ConfigHelper {
		return $this->get( 'Translate:ConfigHelper' );
	}

	/** @since 2022.06 */
	public function getCsvTranslationImporter(): CsvTranslationImporter {
		return $this->get( 'Translate:CsvTranslationImporter' );
	}

	/** @since 2021.10 */
	public function getEntitySearch(): EntitySearch {
		return $this->get( 'Translate:EntitySearch' );
	}

	public function getExternalMessageSourceStateImporter(): ExternalMessageSourceStateImporter {
		return $this->get( 'Translate:ExternalMessageSourceStateImporter' );
	}

	public function getFileFormatFactory(): FileFormatFactory {
		return $this->get( 'Translate:FileFormatFactory' );
	}

	public function getGroupSynchronizationCache(): GroupSynchronizationCache {
		return $this->get( 'Translate:GroupSynchronizationCache' );
	}

	/** @since 2023.03 */
	public function getHookRunner(): HookRunner {
		return $this->get( 'Translate:HookRunner' );
	}

	/** @since 2022.06 */
	public function getMessageBundleStore(): MessageBundleStore {
		return $this->get( 'Translate:MessageBundleStore' );
	}

	/** @since 2020.10 */
	public function getMessageIndex(): MessageIndex {
		return $this->get( 'Translate:MessageIndex' );
	}

	/** @since 2022.07 */
	public function getMessageGroupReviewStore(): MessageGroupReviewStore {
		return $this->get( 'Translate:MessageGroupReviewStore' );
	}

	/** @since 2023.02 */
	public function getMessageGroupStatsTableFactory(): MessageGroupStatsTableFactory {
		return $this->get( 'Translate:MessageGroupStatsTableFactory' );
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

	/** @since 2023.08 */
	public function getRevTagStore(): RevTagStore {
		return $this->get( 'Translate:RevTagStore' );
	}

	/** @since 2023.05 */
	public function getTranslatableBundleExporter(): TranslatableBundleExporter {
		return $this->get( 'Translate:TranslatableBundleExporter' );
	}

	/** @since 2023.05 */
	public function getTranslatableBundleImporter(): TranslatableBundleImporter {
		return $this->get( 'Translate:TranslatableBundleImporter' );
	}

	/** @since 2022.03 */
	public function getTranslatableBundleFactory(): TranslatableBundleFactory {
		return $this->get( 'Translate:TranslatableBundleFactory' );
	}

	/** @since 2022.02 */
	public function getTranslatableBundleMover(): TranslatableBundleMover {
		return $this->get( 'Translate:TranslatableBundleMover' );
	}

	/** @since 2022.10 */
	public function getTranslatableBundleStatusStore(): TranslatableBundleStatusStore {
		return $this->get( 'Translate:TranslatableBundleStatusStore' );
	}

	public function getTranslatablePageParser(): TranslatablePageParser {
		return $this->get( 'Translate:TranslatablePageParser' );
	}

	/** @since 2022.03 */
	public function getTranslatablePageStore(): TranslatablePageStore {
		return $this->get( 'Translate:TranslatablePageStore' );
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
