<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Diagnostics;

use MediaWiki\CommentStore\CommentStore;
use MediaWiki\CommentStore\CommentStoreComment;
use MediaWiki\Content\ContentHandler;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\MessageGroups\AggregateMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\MessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\WikiPageMessageGroup;
use MediaWiki\Extension\Translate\MessageLoading\MessageCollection;
use MediaWiki\Extension\Translate\PageTranslation\RenderTranslationPageJob;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\Extension\Translate\Utilities\MediaWikiPlural;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\JobQueue\JobQueueGroup;
use MediaWiki\Language\FormatterFactory;
use MediaWiki\Page\WikiPageFactory;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Title\Title;
use RuntimeException;
use Throwable;

/**
 * Marks translations using {{PLURAL:}} as outdated so that translators review them,
 * for example after the plural rules of a language have changed.
 * @license GPL-2.0-or-later
 * @since 2026.11
 * @author Huei Tan
 */
class FuzzyPluralTranslationsMaintenanceScript extends BaseMaintenanceScript {
	private const FLAG_ALREADY_FUZZY = 'already fuzzy';
	private const FLAG_ALREADY_PROCESSED = 'already processed';

	private FormatterFactory $formatterFactory;
	private JobQueueGroup $jobQueueGroup;
	private RevisionLookup $revisionLookup;
	private WikiPageFactory $wikiPageFactory;

	public function __construct() {
		parent::__construct();
		$this->addDescription(
			'Mark translations that use {{PLURAL:}} as outdated in the given language and message group, ' .
			'so that translators review them. Dry run by default.'
		);
		$this->addOption(
			'language',
			'Language code of the translations to mark for review',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'group',
			'Message group ID. Aggregate message groups are expanded recursively.',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'summary',
			'Edit summary (wikitext), for example Plural forms need review. See [[Portal:ceb#anchor]]',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'really',
			'(optional) Really make the edits, no dry run'
		);
		$this->requireExtension( 'Translate' );
	}

	private function initServices(): void {
		$services = $this->getServiceContainer();
		$this->formatterFactory = $services->getFormatterFactory();
		$this->jobQueueGroup = $services->getJobQueueGroup();
		$this->revisionLookup = $services->getRevisionLookup();
		$this->wikiPageFactory = $services->getWikiPageFactory();
	}

	public function execute(): void {
		$this->initServices();
		$languageCode = $this->getOption( 'language' );
		$summary = $this->validateSummary( $this->getOption( 'summary' ) );
		$this->validateLanguage( $languageCode );

		$groupId = $this->getOption( 'group' );
		$group = MessageGroups::getGroup( $groupId );
		if ( $group === null ) {
			$this->fatalError( "Unknown message group: $groupId" );
		}

		$dryRun = !$this->hasOption( 'really' );
		$this->output( "Edit summary: $summary\n" );

		$groups = $this->getGroupsToProcess( $group, $languageCode );
		[ $candidates, $stats ] = $this->findCandidates( $groups, $languageCode, $summary );

		ksort( $candidates, SORT_STRING );
		$failures = 0;
		if ( $dryRun ) {
			$this->listCandidates( $candidates );
		} else {
			$failures = $this->markCandidates( $candidates, $languageCode, $summary );
		}

		$this->outputStats( $stats );

		if ( $dryRun ) {
			$this->output( "Dry run: no changes were made. Use --really to make the edits.\n" );
		} elseif ( $failures > 0 ) {
			$this->fatalError( "$failures edit(s) failed." );
		}
	}

	private function validateSummary( string $summary ): string {
		$summary = trim( $summary );
		if ( $summary === '' ) {
			$this->fatalError( 'The edit summary must not be empty.' );
		}

		// Longer summaries are truncated by CommentStore, which could cut off the review link
		$limit = CommentStore::COMMENT_CHARACTER_LIMIT;
		if ( mb_strlen( $summary ) > $limit ) {
			$this->fatalError( "The edit summary must not be longer than $limit characters." );
		}

		return $summary;
	}

	private function validateLanguage( string $languageCode ): void {
		if ( $languageCode === $this->getConfig()->get( 'TranslateDocumentationLanguageCode' ) ) {
			$this->fatalError( "Message documentation ($languageCode) cannot be marked for review." );
		}

		if ( !Utilities::isSupportedLanguageCode( $languageCode ) ) {
			$this->fatalError( "Invalid language code: $languageCode" );
		}
	}

	/**
	 * Expands aggregate groups to their leaf groups and drops the groups
	 * where the language should not be translated.
	 * @return MessageGroup[]
	 */
	private function getGroupsToProcess( MessageGroup $group, string $languageCode ): array {
		$leaves = [];
		if ( $group instanceof AggregateMessageGroup ) {
			// Discouraged subgroups are already left out by AggregateMessageGroup::getGroups
			$tree = MessageGroups::subGroups( $group );
			array_walk_recursive( $tree, static function ( MessageGroup $subgroup ) use ( &$leaves ) {
				if ( !$subgroup instanceof AggregateMessageGroup ) {
					$leaves[$subgroup->getId()] = $subgroup;
				}
			} );
		} else {
			$leaves[$group->getId()] = $group;
		}

		$groups = [];
		foreach ( $leaves as $id => $leaf ) {
			$skipReason = $this->getSkipReason( $leaf, $languageCode );
			if ( $skipReason !== null ) {
				$this->output( "Skipping group $id: $skipReason\n" );
				continue;
			}
			$groups[$id] = $leaf;
		}

		return $groups;
	}

	private function getSkipReason( MessageGroup $group, string $languageCode ): ?string {
		if ( MessageGroups::getPriority( $group ) === 'discouraged' ) {
			return 'translation is discouraged';
		}

		if ( $group->getSourceLanguage() === $languageCode ) {
			return 'source language of the group';
		}

		$translatableLanguages = $group->getTranslatableLanguages();
		if ( $translatableLanguages !== MessageGroup::DEFAULT_LANGUAGES
			&& !isset( $translatableLanguages[$languageCode] )
		) {
			return 'language is not translatable in the group';
		}

		return null;
	}

	/**
	 * @param MessageGroup[] $groups
	 * @return array Candidates indexed by title text, and per group statistics
	 */
	private function findCandidates( array $groups, string $languageCode, string $summary ): array {
		$fuzzyBot = FuzzyBot::getUser();

		$candidates = [];
		$stats = [];
		$seen = [];
		foreach ( $groups as $id => $group ) {
			$stats[$id] = [ 'inspected' => 0, 'plural' => 0, 'fuzzy' => 0, 'processed' => 0 ];

			$collection = $group->initCollection( $languageCode );
			$collection->filter( MessageCollection::FILTER_HAS_TRANSLATION, MessageCollection::INCLUDE_MATCHING );
			$collection->loadTranslations();
			$titles = $collection->keys();

			foreach ( $collection as $key => $message ) {
				$title = Title::newFromLinkTarget( $titles[$key] );
				$titleText = $title->getPrefixedText();
				// The same message can be in multiple subgroups of an aggregate group
				if ( isset( $seen[$titleText] ) ) {
					continue;
				}
				$seen[$titleText] = true;
				$stats[$id]['inspected']++;

				$translation = $message->translation();
				if ( $translation === null || !MediaWikiPlural::hasPlural( $translation ) ) {
					continue;
				}
				$stats[$id]['plural']++;

				$isFuzzy = $message->hasTag( 'fuzzy' );
				if ( $isFuzzy ) {
					$stats[$id]['fuzzy']++;
				}

				// Allows re-running the script after a partial failure
				$revision = $this->revisionLookup->getRevisionById( (int)$message->getProperty( 'revision' ) );
				$alreadyProcessed = $revision
					&& $revision->getUser()?->equals( $fuzzyBot )
					&& $revision->getComment()?->text === $summary;
				if ( $alreadyProcessed ) {
					$stats[$id]['processed']++;
				}

				$candidates[$titleText] = [
					'title' => $title,
					'group' => $group,
					'fuzzy' => $isFuzzy,
					'processed' => $alreadyProcessed,
				];
			}
		}

		return [ $candidates, $stats ];
	}

	private function listCandidates( array $candidates ): void {
		foreach ( $candidates as $titleText => $candidate ) {
			$this->output( $titleText . $this->formatFlags( $candidate ) . "\n" );
		}
	}

	/** @return int Number of failed edits */
	private function markCandidates( array $candidates, string $languageCode, string $summary ): int {
		$failures = 0;
		$translationPages = [];
		foreach ( $candidates as $titleText => $candidate ) {
			$this->output( $titleText . $this->formatFlags( $candidate ) . '... ' );
			if ( $candidate['processed'] ) {
				$this->output( "SKIPPED\n" );
			} else {
				$this->beginTransactionRound( __METHOD__ );
				try {
					$this->markForReview( $candidate['title'], $candidate['fuzzy'], $summary );
					$this->commitTransactionRound( __METHOD__ );
				} catch ( Throwable $e ) {
					$this->rollbackTransactionRound( __METHOD__ );
					$failures++;
					$this->output( "FAILED: {$e->getMessage()}\n" );
					continue;
				}
				$this->output( "OK\n" );
			}

			// Also for skipped translations, in case an earlier run stopped before queueing the jobs
			$group = $candidate['group'];
			if ( $group instanceof WikiPageMessageGroup ) {
				$target = $group->getTitle()->getSubpage( $languageCode );
				if ( $target ) {
					$translationPages[$target->getPrefixedText()] = $target;
				}
			}
		}

		$this->queueRenderJobs( $translationPages, $summary );

		return $failures;
	}

	private function markForReview( Title $title, bool $isFuzzy, string $summary ): void {
		$comment = CommentStoreComment::newUnsavedComment( $summary );
		$fuzzyBot = FuzzyBot::getUser();

		$wikiPage = $this->wikiPageFactory->newFromTitle( $title );
		if ( $isFuzzy ) {
			// Only record the summary. The fuzzy tag is copied to the dummy revision
			// in HookHandler::onRevisionRecordInserted.
			$wikiPage->newPageUpdater( $fuzzyBot )->saveDummyRevision( $comment, EDIT_FORCE_BOT );
			return;
		}

		// Like FuzzyTranslationsMaintenanceScript::updateMessage, TranslateEditAddons::onSaveComplete
		// sets the fuzzy tag because of the marker. If the text already has the marker without
		// the page being fuzzy, the marker is doubled so that the save is not a null edit.
		// Exports remove all markers.
		$text = Utilities::getTextFromTextContent( $wikiPage->getContent() );
		$content = ContentHandler::makeContent( TRANSLATE_FUZZY . $text, $title );
		$updater = $wikiPage->newPageUpdater( $fuzzyBot );
		$updater
			->setContent( SlotRecord::MAIN, $content )
			->saveRevision( $comment, EDIT_FORCE_BOT | EDIT_UPDATE );

		$status = $updater->getStatus();
		if ( !$status->isOK() ) {
			throw new RuntimeException( $this->formatterFactory
				->getStatusFormatter( RequestContext::getMain() )
				->getWikiText( $status ) );
		}
	}

	/**
	 * Translation pages are not re-rendered for FuzzyBot edits to translation units,
	 * so outdated units would not be shown as such until the next edit.
	 * Like for unit edits in PageTranslation\Hooks, the render uses the summary of the unit edits.
	 * @param Title[] $translationPages
	 */
	private function queueRenderJobs( array $translationPages, string $summary ): void {
		$jobs = [];
		foreach ( $translationPages as $target ) {
			if ( $target->exists() ) {
				$job = RenderTranslationPageJob::newJob( $target );
				$job->setSummary( $summary );
				$jobs[] = $job;
			}
		}

		if ( $jobs ) {
			$this->jobQueueGroup->push( $jobs );
			$this->output( 'Queued ' . count( $jobs ) . " translation page render job(s).\n" );
		}
	}

	private function formatFlags( array $candidate ): string {
		$flags = [];
		if ( $candidate['fuzzy'] ) {
			$flags[] = self::FLAG_ALREADY_FUZZY;
		}
		if ( $candidate['processed'] ) {
			$flags[] = self::FLAG_ALREADY_PROCESSED;
		}

		return $flags ? ' [' . implode( ', ', $flags ) . ']' : '';
	}

	private function outputStats( array $stats ): void {
		$total = [ 'inspected' => 0, 'plural' => 0, 'fuzzy' => 0, 'processed' => 0 ];
		foreach ( $stats as $id => $groupStats ) {
			$this->output( "Group $id: " . $this->formatStats( $groupStats ) . "\n" );
			foreach ( $groupStats as $name => $value ) {
				$total[$name] += $value;
			}
		}

		$this->output( 'Total: ' . $this->formatStats( $total ) . "\n" );
	}

	private function formatStats( array $stats ): string {
		return "{$stats['inspected']} translations inspected, {$stats['plural']} with PLURAL, " .
			"{$stats['fuzzy']} of those already fuzzy, {$stats['processed']} already processed";
	}
}
