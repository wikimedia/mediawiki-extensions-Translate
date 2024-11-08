<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use LocalizedException;
use MediaWiki\Message\Message;

/**
 * Exception thrown when message group languages do not match the aggregate message group's language
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2024.11
 */
class AggregateGroupLanguageMismatchException extends LocalizedException {
	public function __construct( array $invalidGroupIds, string $aggregateGroupLanguageCode ) {
		parent::__construct(
			[
				'translate-error-aggregategroup-source-language-mismatch',
				Message::listParam( $invalidGroupIds ),
				$aggregateGroupLanguageCode,
				count( $invalidGroupIds )
			]
		);
	}
}
