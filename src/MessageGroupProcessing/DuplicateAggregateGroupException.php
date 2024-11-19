<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use LocalizedException;
use Wikimedia\Message\MessageValue;

/**
 * Exception thrown when a duplicate aggregate group with the given name is found
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2024.11
 */
class DuplicateAggregateGroupException extends LocalizedException {
	public function __construct( string $aggregateGroupName ) {
		parent::__construct(
			MessageValue::new( 'translate-error-duplicate-aggregategroup' )
				->plaintextParams( $aggregateGroupName )
		);
	}
}
