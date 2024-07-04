<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Article;
use MediaWiki\Config\ServiceOptions;
use MediaWiki\User\User;
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
	private IConnectionProvider $connectionProvider;
	/** @var int[] */
	private array $pageTranslationBannerNamespaces;

	public function __construct(
		IConnectionProvider $connectionProvider,
		ServiceOptions $options
	) {
		$this->connectionProvider = $connectionProvider;
		$options->assertRequiredOptions( self::SERVICE_OPTIONS );
		$this->pageTranslationBannerNamespaces = $options->get( 'TranslatePageTranslationBannerNamespaces' );
	}

	/** Determines whether call to action to mark a page for translation should be shown on the article header. */
	public function shouldDisplayPageTranslationBanner( Article $article, User $user ): bool {
		$articleTitle = $article->getTitle();
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
		if ( $page->getReadyTag() !== null ) {
			return false;
		}

		// Don't show the banner to translation administrators
		if ( $user->isAllowed( 'pagetranslation' ) ) {
			return false;
		}

		$dbr = $this->connectionProvider->getReplicaDatabase();
		$fieldValue = $dbr->newSelectQueryBuilder()
			->select( 'rev_id' )
			->from( 'revision' )
			->join( 'actor', null, 'actor.actor_id = revision.rev_actor' )
			->where( [
				'rev_page' => $article->getPage()->getId(),
				'actor.actor_user' => $user->getId(),
				$dbr->expr(
					'rev_timestamp', '>=', $dbr->timestamp( time() - self::RECENT_EDITOR_DAYS * 24 * 3600 )
				)
			] )
			->caller( __METHOD__ )
			->fetchField();

		return $fieldValue !== false;
	}
}
