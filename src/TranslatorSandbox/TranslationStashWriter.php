<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use User;

interface TranslationStashWriter {
	/**
	 * Adds a new translation to the stash. If the same key already exists, the
	 * previous translation and metadata will be replaced with the new one.
	 */
	public function addTranslation( StashedTranslation $item ): void;

	/** Delete all stashed translations for the given user. */
	public function deleteTranslations( User $user ): void;
}
