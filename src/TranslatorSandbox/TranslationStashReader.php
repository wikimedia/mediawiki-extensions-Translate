<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use User;

/*
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.11
 */
interface TranslationStashReader {
	/**
	 * Gets all stashed translations for the given user.
	 *
	 * @return StashedTranslation[]
	 */
	public function getTranslations( User $user ): array;
}
