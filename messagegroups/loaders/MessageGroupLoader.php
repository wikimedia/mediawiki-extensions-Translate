<?php
declare( strict_types = 1 );

/**
 * Interface for message group loaders
 * @since 2024.06
 * @author Abijeet Patro
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
