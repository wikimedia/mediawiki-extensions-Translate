<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use MessageHandle;
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
abstract class TranslatableBundle {
	/** Return the title of the page where the translatable bundle is defined */
	abstract public function getTitle(): Title;

	/**
	 * Return the message group id for the bundle
	 * Note that the message group id may refer to a message group that does not exist.
	 */
	abstract public function getMessageGroupId(): string;

	/**
	 * Return the available translation pages for the bundle
	 * @see Translation page: https://www.mediawiki.org/wiki/Help:Extension:Translate/Glossary
	 * @return Title[]
	 */
	abstract public function getTranslationPages(): array;

	/**
	 * Return the available translation units for the bundle
	 * @see Translation unit: https://www.mediawiki.org/wiki/Help:Extension:Translate/Glossary
	 * @return Title[]
	 */
	abstract public function getTranslationUnitPages( ?string $code = null ): array;

	/** Check if this translatable bundle is moveable */
	abstract public function isMoveable(): bool;

	/** Check if this is a deletable translatable bundle */
	abstract public function isDeletable(): bool;

	protected function getTranslationUnitPagesByTitle( Title $title, ?string $code = null ): array {
		$dbw = wfGetDB( DB_PRIMARY );

		$base = $title->getPrefixedDBkey();
		// Including the / used as separator
		$baseLength = strlen( $base ) + 1;

		if ( $code === null ) {
			$like = $dbw->buildLike( "$base/", $dbw->anyString() );
		} else {
			$like = $dbw->buildLike( "$base/", $dbw->anyString(), "/$code" );
		}

		$fields = [ 'page_namespace', 'page_title' ];
		$conds = [
			'page_namespace' => NS_TRANSLATIONS,
			'page_title ' . $like
		];
		$res = $dbw->select( 'page', $fields, $conds, __METHOD__ );

		// Only include pages which belong to this translatable page.
		// Problematic cases are when pages Foo and Foo/bar are both
		// translatable. Then when querying for Foo, we also get units
		// belonging to Foo/bar.
		$units = [];
		foreach ( $res as $row ) {
			$title = Title::newFromRow( $row );

			// Strip the language code and the name of the
			// translatable to get plain translation unit id
			$handle = new MessageHandle( $title );
			$key = substr( $handle->getKey(), $baseLength );
			if ( strpos( $key, '/' ) !== false ) {
				// Probably belongs to translatable subpage
				continue;
			}

			// We have a match :)
			$units[] = $title;
		}

		return $units;
	}
}
