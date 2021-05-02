<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use FormatJson;
use Language;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\Extension\Translate\Utilities\ParallelExecutor;
use MediaWiki\MediaWikiServices;
use MessageGroup;
use MessageGroups;
use MessageGroupStats;
use MessageHandle;
use TMessage;
use WikiMap;

/**
 * @since 2020.11
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class ExportTtmServerDumpMaintenanceScript extends BaseMaintenanceScript {
	/** @var Language */
	private $contentLanguage;

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Creates a dump file that can be imported to a TTMServer' );

		$this->addOption(
			'output-directory',
			'Which directory to output files to',
			self::REQUIRED,
			self::HAS_ARG,
			'o'
		);
		$this->addOption(
			'threads',
			'How many threads to use',
			self::OPTIONAL,
			self::HAS_ARG,
			'n'
		);

		$availableMethods = array_keys( $this->getAvailableCompressionWrappers() );
		$values = count( $availableMethods ) ? implode( ', ', $availableMethods ) : 'NONE';
		$this->addOption(
			'compress',
			"Use a compression filter. Possible values: $values",
			self::OPTIONAL,
			self::HAS_ARG,
			'c'
		);

		$this->requireExtension( 'Translate' );
	}

	/** @return string[] */
	private function getAvailableCompressionWrappers(): array {
		$out = [];
		$filters = stream_get_filters();
		foreach ( $filters as $f ) {
			if ( preg_match( '/^compress\..+$/', $f ) ) {
				$out[$f] = $f . '://';
			}
		}
		return $out;
	}

	public function execute() {
		$this->contentLanguage = MediaWikiServices::getInstance()->getContentLanguage();

		$threads = (int)$this->getOption( 'threads', 1 );
		$outputDir = $this->getOption( 'output-directory' );
		$requestedWrapper = $this->getOption( 'compress' );
		$availableWrappers = $this->getAvailableCompressionWrappers();
		if ( $requestedWrapper && !isset( $availableWrappers[$requestedWrapper] ) ) {
			$this->fatalError(
				"Compression wrapper '$requestedWrapper' is not supported"
			);
		}
		$wrapper = $availableWrappers[$requestedWrapper] ?? '';
		$suffix = $requestedWrapper ? ".$requestedWrapper" : '';

		$executor = new ParallelExecutor( $threads );

		$groups = $this->getGroupsInPerformanceOrder();
		foreach ( $groups as $groupId => $group ) {
			$path = $wrapper . rtrim( $outputDir, '/' ) . '/' . $groupId . '.json' . $suffix;

			$executor->runInParallel(
				function ( int $pid ) use ( $groupId ) {
					$this->output( "Forked process $pid to process $groupId\n" );
				},
				function () use ( $group, $path ) {
					$output = FormatJson::encode(
						$this->getOutput( $group ),
						true,
						FormatJson::ALL_OK
					);
					file_put_contents( $path, $output );
				}
			);
		}

		$this->output( "Done.\n" );
	}

	/**
	 * Return groups sorted by number of messages.
	 *
	 * For parallel processing, it makes sense to process large groups first so that smaller
	 * ones can execute in parallel threads, rather than waiting for large group(s) to process
	 * while other threads have nothing to do. Do not spend time on gathering statistics in case
	 * they are not present.
	 *
	 * @return MessageGroup[]
	 */
	private function getGroupsInPerformanceOrder(): array {
		$groupStats = MessageGroupStats::forLanguage(
			$this->contentLanguage->getCode(),
			MessageGroupStats::FLAG_CACHE_ONLY
		);

		uasort(
			$groupStats,
			function ( array $a, array $b ): int {
				return -1 * $this->sortGroupsBySize( $a, $b );
			}
		);

		$groups = [];
		foreach ( array_keys( $groupStats ) as $groupId ) {
			$group = MessageGroups::getGroup( $groupId );
			if ( $group->isMeta() ) {
				continue;
			}

			$groups[$group->getId()] = $group;
		}

		return $groups;
	}

	private function sortGroupsBySize( array $a, array $b ): int {
		return $a[MessageGroupStats::TOTAL] <=> $b[MessageGroupStats::TOTAL];
	}

	private function getOutput( MessageGroup $group ): array {
		$out = [];

		$groupId = $group->getId();
		$sourceLanguage = $group->getSourceLanguage();

		$stats = MessageGroupStats::forGroup( $groupId );
		$collection = $group->initCollection( $sourceLanguage );
		foreach ( $stats as $language => $numbers ) {
			if ( $numbers[MessageGroupStats::TRANSLATED] === 0 ) {
				continue;
			}

			$collection->resetForNewLanguage( $language );
			$collection->filter( 'ignored' );
			$collection->filter( 'translated', false );
			$collection->loadTranslations();

			foreach ( $collection->keys() as $mkey => $titleValue ) {
				$handle = new MessageHandle( $titleValue );
				/** @var TMessage $message */
				$message = $collection[$mkey];

				if ( !isset( $out[$mkey] ) ) {
					$out[$mkey] = [
						'wikiId' => WikiMap::getCurrentWikiId(),
						'title' => $handle->getTitleForBase()->getPrefixedText(),
						'sourceLanguage' => $sourceLanguage,
						'primaryGroup' => $groupId,
						'values' => [],
					];
				}

				$out[$mkey]['values'][] = [
					'language' => $language,
					'value' => $message->translation(),
					'revision' => $message->getProperty( 'revision' ),
				];
			}
		}

		return array_values( $out );
	}
}
