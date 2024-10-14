<?php
declare( strict_types = 1 );

/**
 * Contains configurations to use when setting up the test environment for MessageGroup testing
 * @author Abijeet Patro
 * @since 2024.06
 * @license GPL-2.0-or-later
 */
final class MessageGroupTestConfig {
	public ?array $groups = null;
	public array $groupInitLoaders = [];
	public bool $skipMessageIndexRebuild = false;
	public array $translateGroupFiles = [];
}
