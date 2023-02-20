<?php
/**
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Validation;

use MediaWiki\Extension\Translate\MessageLoading\Message;

/**
 * Interim interface for message validators.
 *
 * In the future, it is expected that this will be deprecated and replaced with
 * a MessageRecordValidator interface.
 *
 * @since 2020.06
 */
interface MessageValidator {
	public function getIssues( Message $message, string $targetLanguage ): ValidationIssues;
}
