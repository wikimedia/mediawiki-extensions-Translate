<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @defgroup TTMServer The Translate extension translation memory interface
 */

use MediaWiki\MediaWikiServices;

/**
 * Some general static methods for instantiating TTMServer and helpers.
 * @since 2012-01-28
 * Rewritten in 2012-06-27.
 * @ingroup TTMServer
 */
abstract class TTMServer {
	/** @var array */
	protected $config;

	/** @param array $config */
	public function __construct( array $config ) {
		$this->config = $config;
	}

	/**
	 * @param array[] $suggestions
	 * @return array[]
	 */
	public static function sortSuggestions( array $suggestions ) {
		usort( $suggestions, static function ( $a, $b ) {
			return $b['quality'] <=> $a['quality'];
		} );

		return $suggestions;
	}

	/**
	 * Hook: ArticleDeleteComplete
	 * @param WikiPage $wikipage
	 */
	public static function onDelete( WikiPage $wikipage ) {
		$handle = new MessageHandle( $wikipage->getTitle() );
		$job = TTMServerMessageUpdateJob::newJob( $handle, 'delete' );
		MediaWikiServices::getInstance()->getJobQueueGroup()->push( $job );
	}

	/**
	 * Called from TranslateEditAddons::onSave
	 * @param MessageHandle $handle
	 */
	public static function onChange( MessageHandle $handle ) {
		$job = TTMServerMessageUpdateJob::newJob( $handle, 'refresh' );
		MediaWikiServices::getInstance()->getJobQueueGroup()->push( $job );
	}

	/**
	 * @param MessageHandle $handle
	 * @param array $old
	 */
	public static function onGroupChange( MessageHandle $handle, $old ) {
		if ( $old === [] ) {
			// Don't bother for newly added messages
			return;
		}

		$job = TTMServerMessageUpdateJob::newJob( $handle, 'rebuild' );
		MediaWikiServices::getInstance()->getJobQueueGroup()->push( $job );
	}
}
