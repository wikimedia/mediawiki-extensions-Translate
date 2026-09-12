<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Extension\Translate\MessageGroups\MessageGroup;

/**
 * Interface for message group loaders
 * @since 2024.06
 * @author Abijeet Patro
 * @author Siebrand Mazeland
 * @license GPL-2.0-or-later
 */
interface MessageGroupLoader {
	/**
	 * Fetches and returns an array of MessageGroups.
	 * @return MessageGroup[] Array of message groups with group id as the key
	 * @note Do not return an indexed based array as that would cause MessageGroups to
	 * be overwritten.
	 */
	public function getGroups(): array;
}

/** @deprecated class alias since 2026.09 */
class_alias( MessageGroupLoader::class, 'MessageGroupLoader' );
