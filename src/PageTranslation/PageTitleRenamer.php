<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Title;

/**
 * Contains logic to determine the new title of translatable pages and
 * dependent pages being moved
 * @author Niklas Laxström
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2021.09
 */
class PageTitleRenamer {
	public const NO_ERROR = 0;
	public const UNKNOWN_PAGE = 1;
	public const NS_TALK_UNSUPPORTED = 2;
	public const RENAME_FAILED = 3;
	public const INVALID_TITLE = 4;

	private const IMPOSSIBLE = null;
	private $map = [];

	public function __construct( Title $source, Title $target ) {
		$this->map[$source->getNamespace()] = [
			$target->getNamespace(),
			$source->getText(),
			$target->getText(),
		];

		$sourceTalkPage = $source->getTalkPageIfDefined();
		$targetTalkPage = $target->getTalkPageIfDefined();
		if ( $sourceTalkPage ) {
			if ( !$targetTalkPage ) {
				$this->map[$sourceTalkPage->getNamespace()] = [
					self::IMPOSSIBLE,
					null,
					null,
				];
			} else {
				$this->map[$sourceTalkPage->getNamespace()] = [
					$targetTalkPage->getNamespace(),
					$source->getText(),
					$target->getText(),
				];
			}
		}

		$this->map[NS_TRANSLATIONS] = [
			NS_TRANSLATIONS,
			$source->getPrefixedText(),
			$target->getPrefixedText(),
		];

		$this->map[NS_TRANSLATIONS_TALK] = [
			NS_TRANSLATIONS_TALK,
			$source->getPrefixedText(),
			$target->getPrefixedText(),
		];
	}

	public function getNewTitle( Title $title ): Title {
		$instructions = $this->map[$title->getNamespace()] ?? null;
		if ( $instructions === null ) {
			throw new InvalidPageTitleRename(
				'Trying to move a page which is not part of the translatable page', self::UNKNOWN_PAGE
			);
		}

		[ $newNamespace, $search, $replace ] = $instructions;

		if ( $newNamespace === self::IMPOSSIBLE ) {
			throw new InvalidPageTitleRename(
				'Trying to move a talk page to a namespace which does not have talk pages',
				self::NS_TALK_UNSUPPORTED
			);
		}

		if ( $search === $replace ) {
			return Title::makeTitleSafe( $newNamespace, $replace );
		}

		$oldTitleText = $title->getText();
		$searchQuoted = preg_quote( $search, '~' );
		$newText = preg_replace( "~^$searchQuoted~", $replace, $oldTitleText, 1 );

		if ( $oldTitleText === $newText ) {
			throw new InvalidPageTitleRename( 'Renaming failed ', self::RENAME_FAILED );
		}

		$title = Title::makeTitleSafe( $newNamespace, $newText );
		if ( $title === null ) {
			throw new InvalidPageTitleRename( 'Invalid target title', self::INVALID_TITLE );
		}

		return $title;
	}
}
