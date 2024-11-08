<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use LocalizedException;
use MediaWiki\Message\Message;

/**
 * Exception thrown when a message group could not be associated to an aggregate group
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2024.11
 */
class AggregateGroupAssociationFailure extends LocalizedException {
	public function __construct( array $groupIds ) {
		parent::__construct(
			[
				'translate-error-association-failure',
				Message::listParam( $groupIds ),
				count( $groupIds )
			]
		);
	}
}
