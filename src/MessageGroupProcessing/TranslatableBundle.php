<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Title;

/**
 * Translatable bundle represents a message group where its translatable content is
 * defined on a wiki page.
 *
 * This interface was created to support moving message bundles using the code developed for
 * moving translatable pages.
 *
 * See also WikiMessageGroup which is not considered to be a translatable bundle.
 * @author Abijeet Patro
 * @author Niklas Laxström
 * @since 2022.03
 * @license GPL-2.0-or-later
 */
interface TranslatableBundle {
	/** Return the title of the page where the translatable bundle is defined */
	public function getTitle(): Title;

	/**
	 * Return the message group id for the bundle
	 * Note that the message group id may refer to a message group that does not exist.
	 */
	public function getMessageGroupId(): string;

	/**
	 * Return the available translation pages for the bundle
	 * @see Translation page: https://www.mediawiki.org/wiki/Help:Extension:Translate/Glossary
	 * @return Title[]
	 */
	public function getTranslationPages(): array;

	/**
	 * Return the available translation units for the bundle
	 * @see Translation unit: https://www.mediawiki.org/wiki/Help:Extension:Translate/Glossary
	 * @param string $set Can be either 'all', or 'active'
	 * @return Title[]
	 */
	public function getTranslationUnitPages(
		string $set = 'active', ?string $code = null
	): array;
}
