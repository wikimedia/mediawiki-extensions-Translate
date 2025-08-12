<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use JobQueueGroup;
use MediaWiki\Extension\Translate\MessageGroupProcessing\DeleteTranslatableBundleJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\SubpageListBuilder;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleFactory;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Title\Title;
use MediaWiki\User\UserIdentity;
use Wikimedia\ObjectCache\BagOStuff;

/**
 * Contains the core logic to delete translatable bundles or translation pages
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2023.10
 */
class TranslatableBundleDeleter {
	private BagOStuff $mainCache;
	private JobQueueGroup $jobQueueGroup;
	private SubpageListBuilder $subpageBuilder;
	private TranslatableBundleFactory $bundleFactory;

	public function __construct(
		BagOStuff $mainCache,
		JobQueueGroup $jobQueueGroup,
		SubpageListBuilder $subpageBuilder,
		TranslatableBundleFactory $bundleFactory
	) {
		$this->mainCache = $mainCache;
		$this->jobQueueGroup = $jobQueueGroup;
		$this->subpageBuilder = $subpageBuilder;
		$this->bundleFactory = $bundleFactory;
	}

	/**
	 * Returns list of pages to be deleted based on whether the page being deleted is a translation page, translatable
	 * page or a translatable bundle.
	 * @param Title $title
	 * @param string|null $languageCode
	 * @param bool $isTranslationPage
	 * @return array<string,Title[]>
	 */
	public function getPagesForDeletion( Title $title, ?string $languageCode, bool $isTranslationPage ): array {
		if ( $isTranslationPage ) {
			$resultSet = $this->subpageBuilder->getEmptyResultSet();

			[ $titleKey, ] = Utilities::figureMessage( $title->getPrefixedDBkey() );
			$translatablePage = TranslatablePage::newFromTitle( Title::newFromText( $titleKey ) );

			$resultSet['translationPages'] = [ $title ];
			$resultSet['translationUnitPages'] = $translatablePage->getTranslationUnitPages( $languageCode );
			return $resultSet;
		} else {
			$bundle = $this->bundleFactory->getValidBundle( $title );
			return $this->subpageBuilder->getSubpagesPerType( $bundle, false );
		}
	}

	/** Creates the necessary jobs required to delete translation, translatable pages or message bundles. */
	public function deleteAsynchronously(
		Title $title,
		bool $isTranslation,
		UserIdentity $user,
		array $subpageList,
		bool $deleteSubpages,
		string $reason,
		array $userSessionInfo,
	): void {
		$jobs = [];
		$base = $title->getPrefixedText();
		$bundle = $this->getValidBundleFromTitle( $title, $isTranslation );
		$bundleType = get_class( $bundle );

		foreach ( $subpageList[ 'translationPages' ] as $old ) {
			$jobs[$old->getPrefixedText()] = DeleteTranslatableBundleJob::newJob(
				$old, $base, $bundleType, $isTranslation, $user, $reason, $userSessionInfo
			);
		}

		foreach ( $subpageList[ 'translationUnitPages' ] as $old ) {
			$jobs[$old->getPrefixedText()] = DeleteTranslatableBundleJob::newJob(
				$old, $base, $bundleType, $isTranslation, $user, $reason, $userSessionInfo
			);
		}

		if ( $deleteSubpages ) {
			foreach ( $subpageList[ 'normalSubpages' ] as $old ) {
				$jobs[$old->getPrefixedText()] = DeleteTranslatableBundleJob::newJob(
					$old, $base, $bundleType, $isTranslation, $user, $reason, $userSessionInfo
				);
			}
		}

		if ( !$isTranslation ) {
			$jobs[$title->getPrefixedText()] = DeleteTranslatableBundleJob::newJob(
				$title, $base, $bundleType, false, $user, $reason, $userSessionInfo
			);
		}

		$this->jobQueueGroup->push( $jobs );

		$this->mainCache->set(
			$this->mainCache->makeKey( 'pt-base', $title->getPrefixedText() ),
			array_keys( $jobs ),
			6 * $this->mainCache::TTL_HOUR
		);

		if ( !$isTranslation ) {
			$this->bundleFactory->getStore( $bundle )->delete( $title );
		}
	}

	private function getValidBundleFromTitle( Title $bundleTitle, bool $isTranslation ): TranslatableBundle {
		if ( $isTranslation ) {
			[ $key, ] = Utilities::figureMessage( $bundleTitle->getPrefixedDBkey() );
			$bundleTitle = Title::newFromText( $key );
		}

		return $this->bundleFactory->getValidBundle( $bundleTitle );
	}
}
