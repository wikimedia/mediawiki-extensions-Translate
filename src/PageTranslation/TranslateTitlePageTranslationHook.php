<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Page\PageIdentity;

/**
 * This is a hook handler interface, see docs/Hooks.md in core.
 * Use the hook name "TranslateTitlePageTranslation" to register handlers implementing this interface.
 *
 * @stable to implement
 * @ingroup Hooks
 * @author MusikAnimal
 * @license GPL-2.0-or-later
 * @since 2025.09
 */
interface TranslateTitlePageTranslationHook {
	/**
	 * Hook to control the translatability of page titles at Special:PageTranslation?do=mark
	 *
	 * @param TranslateTitleEnum &$state Enum value that controls the state of the 'translatetitle' option.
	 * @param PageIdentity $page The page being marked for translation.
	 */
	public function onTranslateTitlePageTranslation( TranslateTitleEnum &$state, PageIdentity $page ): void;
}
