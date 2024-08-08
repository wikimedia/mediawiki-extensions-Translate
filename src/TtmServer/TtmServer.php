<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\MediaWikiServices;
use WikiPage;

/**
 * TtmServer - The Translate extension translation memory interface
 * Some general static methods for instantiating TtmServer and helpers.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @defgroup TtmServer The Translate extension translation memory interface
 * @ingroup TTMServerp
 */
abstract class TtmServer {
	protected array $config;

	public function __construct( array $config ) {
		$this->config = $config;
	}

	/**
	 * @param array[] $suggestions
	 * @return array[]
	 */
	public static function sortSuggestions( array $suggestions ): array {
		usort( $suggestions, static function ( array $a, array $b ) {
			return $b['quality'] <=> $a['quality'];
		} );

		return $suggestions;
	}

	/** Hook: ArticleDeleteComplete */
	public static function onDelete( WikiPage $wikipage ): void {
		$handle = new MessageHandle( $wikipage->getTitle() );
		$job = TtmServerMessageUpdateJob::newJob( $handle, 'delete' );
		MediaWikiServices::getInstance()->getJobQueueGroup()->push( $job );
	}

	/** Called from TranslateEditAddons::onSave */
	public static function onChange( MessageHandle $handle ): void {
		$job = TtmServerMessageUpdateJob::newJob( $handle, 'refresh' );
		MediaWikiServices::getInstance()->getJobQueueGroup()->push( $job );
	}

	public static function onGroupChange( MessageHandle $handle, array $old ): void {
		if ( $old === [] ) {
			// Don't bother for newly added messages
			return;
		}

		$job = TtmServerMessageUpdateJob::newJob( $handle, 'rebuild' );
		MediaWikiServices::getInstance()->getJobQueueGroup()->push( $job );
	}
}
