<?php
/**
 * Job for updating translation pages.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2008-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Job for updating translation pages when translation or message definition changes.
 *
 * @ingroup JobQueue
 */
class MessageUpdateJob extends Job {
	public static function newJob( Title $target, $content, $fuzzy = false ) {
		$params = [
			'content' => $content,
			'fuzzy' => $fuzzy,
		];

		$job = new self( $target, $params );

		return $job;
	}

	public static function newRenameJob( Title $target, $targetStr, $replacement,
		$fuzzy, $content, $otherLangContents = []
	) {
		$params = [
			'target' => $targetStr,
			'replacement' => $replacement,
			'fuzzy' => $fuzzy,
			'rename' => 'rename',
			'content' => $content,
			'titleDbKey' => $target->getDBkey(),
			'otherLangs' => $otherLangContents
		];

		$job = new self( $target, $params );

		return $job;
	}

	/**
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params = [] ) {
		parent::__construct( __CLASS__, $title, $params );
	}

	public function run() {
		$params = $this->params;
		$user = FuzzyBot::getUser();
		$flags = EDIT_FORCE_BOT;
		$isRename = $params['rename'] ?? false;
		$isFuzzy = $params['fuzzy'] ?? false;
		$otherLangs = $params['otherLangs'] ?? [];

		if ( $isRename ) {
			$success = $this->handleRename( $params['target'], $params['replacement'],
				$params['namespace'], $params['titleDbKey'], $user );
			if ( !$success ) {
				return true;
			}
		}

		$title = $this->title;
		$wikiPage = WikiPage::factory( $title );
		$summary = wfMessage( 'translate-manage-import-summary' )
			->inContentLanguage()->plain();
		$content = ContentHandler::makeContent( $params['content'], $title );
		$wikiPage->doEditContent( $content, $summary, $flags, false, $user );

		if ( $isRename ) {
			// Update other language content if present.
			$this->processLangChanges( $otherLangs, $params['replacement'], $params['namespace'],
				$summary, $flags, $user );
		}

		if ( $isFuzzy ) {
			$success = $this->handleFuzzy( $title );
			if ( !$success ) {
				return true;
			}
		}

		return true;
	}

	/**
	 * Handles renames
	 * @param string $target
	 * @param string $replacement
	 * @param int $namespace
	 * @param string $titleDbKey
	 * @param User $user
	 * @return bool
	 */
	private function handleRename( $target, $replacement, $namespace,
		$titleDbKey, User $user
	) {
		global $wgContLang;
		$newSourceTitle = null;

		$isCap = MWNamespace::isCapitalized( $namespace );
		if ( $isCap ) {
			$target = $wgContLang->ucfirst( $target );
			$replacement = $wgContLang->ucfirst( $replacement );
		}

		$target = self::sanitize( $target );
		$replacement = self::sanitize( $replacement );

		$movableTitles = TranslateReplaceTitle::getTitlesForMove( $target,
				[ $namespace ], $replacement );

		if ( $movableTitles === [] ) {
			error_log( "MessageUpdateJob:: No moveable titles found with target text - '$target'." );
			return true;
		}

		$renameSummary = wfMessage( 'translate-manage-import-rename-summary' )
			->inContentLanguage()->plain();

		foreach ( $movableTitles as $mTitle ) {
			/**
			 * @var Title $oldTitle
			 * @var Title $newTitle
			 */
			list( $oldTitle, $newTitle ) = $mTitle;
			$mv = new MovePage( $oldTitle, $newTitle );

			$status = $mv->move( $user, $renameSummary, false );
			if ( !$status->isOK() ) {
				$entry = new ManualLogEntry( 'messageupdatejob', 'movenok' );
				$entry->setPerformer( $user );
				$entry->setTarget( $oldTitle );
				$entry->setParameters( [
					'target' => $newTitle,
					'error' => $status->getErrorsArray(),
				] );
				$logid = $entry->insert();
				$entry->publish( $logid );
			}

			if ( !$newSourceTitle && $oldTitle->getDBkey() === $titleDbKey ) {
				$newSourceTitle = $newTitle;
			}
		}

		if ( $newSourceTitle ) {
			$this->title = $newSourceTitle;
		} else {
			// This means that the old source Title was never moved
			// which is not possible but handle it.
			error_log( "MessageUpdateJob:: Source title: '" . $this->title->getFullText() .
				"' was not in the list of moveable titles." );
			return false;
		}

		return true;
	}

	/**
	 * Handles fuzzying
	 * @param Title $title
	 * @return bool
	 */
	private function handleFuzzy( $title ) {
		// NOTE: message documentation is excluded from fuzzying!
		global $wgTranslateDocumentationLanguageCode;
		$handle = new MessageHandle( $title );
		$key = $handle->getKey();

		$languages = TranslateUtils::getLanguageNames( 'en' );
		unset( $languages[$wgTranslateDocumentationLanguageCode] );
		$languages = array_keys( $languages );

		$dbw = wfGetDB( DB_MASTER );
		$fields = [ 'page_id', 'page_latest' ];
		$conds = [ 'page_namespace' => $title->getNamespace() ];

		$pages = [];
		foreach ( $languages as $code ) {
			$otherTitle = Title::makeTitleSafe( $title->getNamespace(), "$key/$code" );
			$pages[$otherTitle->getDBkey()] = true;
		}
		unset( $pages[$title->getDBkey()] );
		if ( $pages === [] ) {
			return true;
		}

		$conds['page_title'] = array_keys( $pages );

		$res = $dbw->select( 'page', $fields, $conds, __METHOD__ );
		$inserts = [];
		foreach ( $res as $row ) {
			$inserts[] = [
				'rt_type' => RevTag::getType( 'fuzzy' ),
				'rt_page' => $row->page_id,
				'rt_revision' => $row->page_latest,
			];
		}

		if ( $inserts === [] ) {
			return true;
		}

		$dbw->replace(
			'revtag',
			[ [ 'rt_type', 'rt_page', 'rt_revision' ] ],
			$inserts,
			__METHOD__
		);
	}

	/**
	 * Updates the content in other language pages.
	 * @param array $langChanges
	 * @param string $titleStr
	 * @param int $groupNamespace
	 * @param string $summary
	 * @param int $flags
	 * @param User $user
	 * @return void
	 */
	private function processLangChanges( $langChanges, $baseTitle,
		$groupNamespace, $summary, $flags, User $user
	) {
		foreach ( $langChanges as $code => $contentStr ) {
			$titleStr = TranslateUtils::title( $baseTitle, $code, $groupNamespace );
			$title = Title::newFromText( $titleStr, $groupNamespace );
			$wikiPage = WikiPage::factory( $title );
			$content = ContentHandler::makeContent( $contentStr, $title );
			$wikiPage->doEditContent( $content, $summary, $flags, false, $user );
		}
	}

	/**
	 * Replace underscore with spaces, and add trailing / to avoid
	 * selecting titles that contain this title.
	 * @param string $str
	 * @return string
	 */
	private static function sanitize( $str ) {
		$str = str_replace( '_', ' ', $str );
		$str = rtrim( $str, '/' ) . '/';

		return $str;
	}
}
