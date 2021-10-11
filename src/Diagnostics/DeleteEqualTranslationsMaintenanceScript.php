<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Diagnostics;

use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MessageCollection;
use MessageGroups;
use SplObjectStorage;
use Title;
use TitleValue;
use TMessage;
use WikiPage;
use const SORT_NUMERIC;

/**
 * @since 2021.01
 * @license GPL-2.0-or-later
 * @author Niklas Laxström
 */
class DeleteEqualTranslationsMaintenanceScript extends BaseMaintenanceScript {
	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Delete translations that are equal to the definition' );

		$this->addOption(
			'group',
			'Which group to scan',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'language',
			'Which language to scan',
			self::REQUIRED,
			self::HAS_ARG
		);
		$this->addOption(
			'really',
			'Delete the listed pages instead of just listing them'
		);
		$this->addOption(
			'comment',
			'Comment for the deletions'
		);

		$this->requireExtension( 'Translate' );
	}

	/** @inheritDoc */
	public function execute() {
		$groupId = $this->getOption( 'group' );
		$language = $this->getOption( 'language' );
		$group = MessageGroups::getGroup( $groupId );
		if ( !$group ) {
			$this->fatalError( "Message group '$groupId' does not exist" );
		}

		$collection = $group->initCollection( $language );
		$equalMessages = $this->getEqualMessages( $collection );
		$equalMessageCount = count( $equalMessages );
		if ( $equalMessageCount === 0 ) {
			$this->output( "No translations equal to definition found\n" );
			return;
		}

		$stats = $this->getUserStats( $equalMessages );
		$this->printUserStats( $stats, $equalMessageCount );
		$this->output( "\n" );
		$this->printMessages( $equalMessages );

		if ( $this->hasOption( 'really' ) ) {
			$comment = $this->getOption( 'comment' ) ?? '';
			$this->deleteMessages( $equalMessages, $comment );
		} else {
			$this->output( "This is a dry-run. Run again with --really to delete these messages\n" );
		}
	}

	private function getEqualMessages( MessageCollection $collection ): SplObjectStorage {
		$collection->filter( 'translated', false );
		$collection->loadTranslations();

		$messages = new SplObjectStorage();
		foreach ( $collection->keys() as $key => $titleValue ) {
			/** @var TMessage $message */
			$message = $collection[$key];

			if ( $message->definition() === $message->translation() ) {
				$messages->attach( $titleValue, $message );
			}
		}

		return $messages;
	}

	private function getUserStats( SplObjectStorage $messages ): array {
		$stats = [];
		foreach ( $messages as $key ) {
			/** @var TMessage $message */
			$message = $messages[$key];
			$index = $message->getProperty( 'last-translator-text' );
			$stats[$index] = ( $stats[$index] ?? 0 ) + 1;
		}

		return $stats;
	}

	private function printUserStats( array $stats, int $equalMessageCount ): void {
		$this->output( "Found $equalMessageCount message(s) created by these user(s):\n" );
		arsort( $stats, SORT_NUMERIC );
		foreach ( $stats as $userName => $count ) {
			$this->output( sprintf( "%6d | %s\n", $count, $userName ) );
		}
	}

	private function printMessages( SplObjectStorage $messages ): void {
		/** @var TitleValue $key */
		foreach ( $messages as $key ) {
			/** @var TMessage $message */
			$message = $messages[$key];
			$title = Title::newFromLinkTarget( $key );
			$this->output(
				sprintf( "== %s ==\n%s\n\n", $title->getPrefixedText(), $message->translation() )
			);
		}
	}

	private function deleteMessages( SplObjectStorage $messages, string $reason ): void {
		$user = FuzzyBot::getUser();

		/** @var TitleValue $key */
		foreach ( $messages as $key ) {
			$title = Title::newFromLinkTarget( $key );
			$page = WikiPage::factory( $title );
			$status = $page->doDeleteArticleReal(
				$reason,
				$user
			);
			if ( $status->isOK() ) {
				$this->output( ".", 'deletions' );
			} else {
				$pageName = $title->getPrefixedText();
				$this->output( "FAILED to delete page $pageName\n" );
			}
		}
	}
}
