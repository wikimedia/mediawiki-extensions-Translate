<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use LocalizedException;

/**
 * Exception thrown when an aggregate message group is not found
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2024.11
 */
class AggregateGroupNotFoundException extends LocalizedException {
	public function __construct( string $aggregateGroupId ) {
		parent::__construct( [ 'translate-error-invalid-aggregategroup', $aggregateGroupId ] );
	}
}
