<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Title;

/**
 * Translatable bundle store manages bundles of certain type.
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.03
 * @license GPL-2.0-or-later
 */
interface TranslatableBundleStore {
	public function move( Title $oldName, Title $newName ): void;
}
