<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use RecentChange;
use Wikimedia\Rdbms\IConnectionProvider;

/**
 * Logic and code to generate various aspects related to how translatable pages are displayed
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2024.05
 * @ingroup PageTranslation
 */
class TranslatablePageView {
	public const SERVICE_OPTIONS = [ 'TranslatePageTranslationBannerNamespaces' ];
	private const RECENT_EDITOR_DAYS = 4;
	private IConnectionProvider $dbProvider;
	private TranslatablePageStateStore $translatablePageStateStore;
	/** @var int[] */
	private array $pageTranslationBannerNamespaces;

	public function __construct(
		IConnectionProvider $dbProvider,
		TranslatablePageStateStore $translatablePageStateStore,
		ServiceOptions $options
	) {
		$this->dbProvider = $dbProvider;
		$this->translatablePageStateStore = $translatablePageStateStore;
		$options->assertRequiredOptions( self::SERVICE_OPTIONS );
		$this->pageTranslationBannerNamespaces = $options->get( 'TranslatePageTranslationBannerNamespaces' );
	}

	/** Determines whether the user should be allowed to update the translation setting */
	public function canManageTranslationSettings( Title $articleTitle, User $user ): bool {
		if ( !$this->isTranslationSettingsAllowedForTitle( $articleTitle ) ) {
			return false;
		}

		// Allow translation administrators and editors to manage the translation settings
		return $user->definitelyCan( 'edit', $articleTitle ) ||
			$user->definitelyCan( 'pagetranslation', $articleTitle );
	}

	/** Determines whether the banner to mark a page for translation should be displayed */
	public function canDisplayTranslationSettingsBanner( Title $articleTitle, User $user ): bool {
		if ( !$user->isNamed() ) {
			return false;
		}

		if ( !$this->isTranslationSettingsAllowedForTitle( $articleTitle ) ) {
			return false;
		}

		if ( $this->translatablePageStateStore->get( $articleTitle ) !== null ) {
			return false;
		}

		$canPerformPageTranslation = $user->isAllowed( 'pagetranslation' );
		// Don't show the banner to translation administrators
		if ( $canPerformPageTranslation ) {
			return false;
		}

		return $this->isRecentEditor( $articleTitle, $user );
	}

	public function isTranslationBannerNamespaceConfigured(): bool {
		return $this->pageTranslationBannerNamespaces !== [];
	}

	private function isRecentEditor( Title $articleTitle, User $user ): bool {
		$dbr = $this->dbProvider->getReplicaDatabase();
		$fieldValue = $dbr->newSelectQueryBuilder()
			->select( 'rc_id' )
			->from( 'recentchanges' )
			->where( [
				'rc_cur_id' => $articleTitle->getId(),
				'rc_source' => [ RecentChange::SRC_NEW, RecentChange::SRC_EDIT ],
				$dbr->expr(
					'rc_timestamp', '>=', $dbr->timestamp( time() - self::RECENT_EDITOR_DAYS * 24 * 3600 )
				),
				'rc_actor' => $user->getActorId()
			] )
			->caller( __METHOD__ )
			->fetchField();

		return $fieldValue !== false;
	}

	private function isTranslationSettingsAllowedForTitle( Title $articleTitle ): bool {
		// TODO: Remove this check for POST action once the feature is enabled on Wikimedia wikis.
		// If a user wants to propose a user subpage for translation, we should let them do so.
		if ( !$articleTitle->inNamespaces( $this->pageTranslationBannerNamespaces ) ) {
			return false;
		}

		if ( $articleTitle->getContentModel() !== CONTENT_MODEL_WIKITEXT ) {
			return false;
		}

		$translationPage = TranslatablePage::isTranslationPage( $articleTitle );
		// This is a translation page, no need to display the CTA
		if ( $translationPage ) {
			return false;
		}

		// Check if the page has the <translate> tags already
		$page = TranslatablePage::newFromTitle( $articleTitle );
		return $page->getReadyTag() === null;
	}
}
