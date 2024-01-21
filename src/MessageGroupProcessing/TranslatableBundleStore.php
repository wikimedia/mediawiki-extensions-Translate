<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Title\Title;

/**
 * Translatable bundle store manages bundles of certain type.
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.03
 * @license GPL-2.0-or-later
 */
interface TranslatableBundleStore {
	public function move( Title $oldName, Title $newName ): void;

	public function handleNullRevisionInsert( TranslatableBundle $bundle, RevisionRecord $revision ): void;

	public function delete( Title $title ): void;
}
