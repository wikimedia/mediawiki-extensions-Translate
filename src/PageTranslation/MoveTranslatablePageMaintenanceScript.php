<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use Closure;
use MalformedTitleException;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Extension\Translate\Utilities\BaseMaintenanceScript;
use MediaWiki\MediaWikiServices;
use Message;
use SplObjectStorage;
use Status;
use Title;
use TitleParser;

class MoveTranslatablePageMaintenanceScript extends BaseMaintenanceScript {
	/** @var TranslatablePageMover */
	private $pageMover;
	/** @var TitleParser */
	private $titleParser;

	public function __construct() {
		parent::__construct();
		$this->addDescription( 'Review and move translatable pages including their subpages' );

		$this->addArg(
			'current-page',
			'Current page name',
			self::REQUIRED
		);

		$this->addArg(
			'new-page',
			'New page name',
			self::REQUIRED
		);

		$this->addArg(
			'user',
			'User performing the move',
			self::REQUIRED
		);

		$this->addOption(
			'reason',
			'Reason for moving the page',
			self::OPTIONAL,
			self::HAS_ARG
		);

		$this->addOption(
			'skip-subpages',
			'Skip moving subpages under the current page'
		);

		$this->addOption(
			'skip-talkpages',
			'Skip moving talk pages under pages being moved'
		);

		$this->requireExtension( 'Translate' );
	}

	/** @inheritDoc */
	public function execute() {
		$this->pageMover = Services::getInstance()->getTranslatablePageMover();

		$mwService = MediaWikiServices::getInstance();
		$this->titleParser = $mwService->getTitleParser();

		$currentPagename = $this->getArg( 0 );
		$newPagename = $this->getArg( 1 );
		$username = $this->getArg( 2 );
		$reason = $this->getOption( 'reason', '' );
		$moveSubpages = !$this->hasOption( 'skip-subpages' );
		$moveTalkpages = !$this->hasOption( 'skip-talkpages' );

		$userFactory = $mwService->getUserFactory();
		$user = $userFactory->newFromName( $username );

		if ( $user === null || !$user->isRegistered() ) {
			$this->fatalError( "User $username does not exist." );
		}

		$outputMsg = "Check if '$currentPagename' can be moved to '$newPagename'";
		$subpageMsg = 'excluding subpages';
		if ( $moveSubpages ) {
			$subpageMsg = 'including subpages';
		}

		$talkpageMsg = 'excluding talkpages';
		if ( $moveTalkpages ) {
			$talkpageMsg = 'including talkpages';
		}

		$this->output( "$outputMsg ($subpageMsg; $talkpageMsg)\n" );

		try {
			$currentTitle = $this->getTitleFromInput( $currentPagename ?? '' );
			$newTitle = $this->getTitleFromInput( $newPagename ?? '' );
		} catch ( MalformedTitleException $e ) {
			$this->error( 'Invalid title: current-page or new-page' );
			$this->fatalError( $e->getMessageObject()->text() );
		}

		// When moving translatable pages from script, remove all limits on the number of
		// pages that can be moved
		$this->pageMover->disablePageMoveLimit();
		try {
			$pageCollection = $this->pageMover->getPageMoveCollection(
				$currentTitle,
				$newTitle,
				$user,
				$reason,
				$moveSubpages,
				$moveTalkpages
			);
		} catch ( ImpossiblePageMove $e ) {
			$fatalErrorMsg = $this->parseErrorMessage( $e->getBlockers() );
			$this->fatalError( $fatalErrorMsg );
		}

		$this->displayPagesToMove( $pageCollection );

		$haveConfirmation = $this->getConfirmation();
		if ( !$haveConfirmation ) {
			$this->output( "Exiting...\n" );
			return;
		}

		$this->output( "Starting page move\n" );

		$pagesToMove = $pageCollection->getListOfPages();

		$this->pageMover->moveSynchronously(
			$currentTitle,
			$newTitle,
			$pagesToMove,
			$user,
			$reason,
			Closure::fromCallable( [ $this, 'progressCallback' ] )
		);

		$this->logSeparator();
		$this->output( "Finished moving '$currentPagename' to '$newPagename' $subpageMsg\n" );
	}

	private function parseErrorMessage( SplObjectStorage $errors ): string {
		$errorMsg = wfMessage( 'pt-movepage-blockers', count( $errors ) )->text() . "\n";
		foreach ( $errors as $title ) {
			$titleText = $title->getPrefixedText();
			$errorMsg .= "$titleText\n";
			$errorMsg .= $errors[ $title ]->getWikiText( false, 'pt-movepage-error-placeholder', 'en' );
			$errorMsg .= "\n";
		}

		return $errorMsg;
	}

	private function progressCallback( Title $previous, Title $new, Status $status, int $total, int $processed ): void {
		$previousTitleText = $previous->getPrefixedText();
		$newTitleText = $new->getPrefixedText();
		$paddedProcessed = str_pad( (string)$processed, strlen( (string)$total ), ' ', STR_PAD_LEFT );
		$progressCounter = "($paddedProcessed/$total)";

		if ( $status->isOK() ) {
			$this->output( "$progressCounter $previousTitleText --> $newTitleText\n" );
		} else {
			$this->output( "$progressCounter Failed to move $previousTitleText to $newTitleText\n" );
			$this->output( "\tReason:" . $status->getWikiText() . "\n" );
		}
	}

	private function displayPagesToMove( PageMoveCollection $pageCollection ): void {
		$infoMessage = "\nThe following pages will be moved:\n";
		$count = 0;
		$subpagesCount = 0;
		$talkpagesCount = 0;

		/** @var PageMoveOperation[][] */
		$pagesToMove = [
			'pt-movepage-list-pages' => [ $pageCollection->getTranslatablePage() ],
			'pt-movepage-list-translation' => $pageCollection->getTranslationPagesPair(),
			'pt-movepage-list-section' => $pageCollection->getUnitPagesPair()
		];

		$subpages = $pageCollection->getSubpagesPair();
		if ( $subpages ) {
			$pagesToMove[ 'pt-movepage-list-other'] = $subpages;
		}

		foreach ( $pagesToMove as $type => $pages ) {
			$lines = [];
			$infoMessage .= $this->getSectionHeader( $type, $pages );
			if ( !count( $pages ) ) {
				continue;
			}

			foreach ( $pages as $pagePairs ) {
				$count++;

				if ( $type === 'pt-movepage-list-other' ) {
					$subpagesCount++;
				}

				$old = $pagePairs->getOldTitle();
				$new = $pagePairs->getNewTitle();

				if ( $new ) {
					$line = '* ' . $old->getPrefixedText() . ' → ' . $new->getPrefixedText();
					if ( $pagePairs->hasTalkpage() ) {
						$count++;
						$talkpagesCount++;
						$line .= ' ' . $this->message( 'pt-movepage-talkpage-exists' )->text();
					}

					$lines[] = $line;
				}
			}

			$infoMessage .= implode( "\n", $lines ) . "\n";
		}

		$translatableSubpages = $pageCollection->getTranslatableSubpages();
		$infoMessage .= $this->getSectionHeader( 'pt-movepage-list-translatable', $translatableSubpages );

		if ( $translatableSubpages ) {
			$lines = [];
			$infoMessage .= $this->message( 'pt-movepage-list-translatable-note' )->text() . "\n";
			foreach ( $translatableSubpages as $page ) {
				$lines[] = '* ' . $page->getPrefixedText();
			}

			$infoMessage .= implode( "\n", $lines ) . "\n";
		}

		$this->output( $infoMessage );

		$this->logSeparator();
		$this->output(
			$this->message( 'pt-movepage-list-count' )
				->numParams( $count, $subpagesCount, $talkpagesCount )
				->text() . "\n"
		);
		$this->logSeparator();
		$this->output( "\n" );
	}

	private function getSectionHeader( string $type, array $pages ): string {
		$infoMessage = $this->getSeparator();
		$pageCount = count( $pages );

		// $type can be: pt-movepage-list-pages, pt-movepage-list-translation, pt-movepage-list-section
		// pt-movepage-list-other
		$infoMessage .= $this->message( $type )->numParams( $pageCount )->text() . "\n\n";
		if ( !$pageCount ) {
			$infoMessage .= $this->message( 'pt-movepage-list-no-pages' )->text() . "\n";
		}

		return $infoMessage;
	}

	private function getConfirmation(): bool {
		$line = self::readconsole( 'Type "MOVE" to begin the move operation: ' );
		return strtolower( $line ) === 'move';
	}

	private function getSeparator( int $width = 15 ): string {
		return str_repeat( '-', $width ) . "\n";
	}

	private function logSeparator( int $width = 15 ): void {
		$this->output( $this->getSeparator( $width ) );
	}

	private function message( string $key ): Message {
		return ( new Message( $key ) )->inLanguage( 'en' );
	}

	private function getTitleFromInput( string $pageName ): Title {
		$titleValue = $this->titleParser->parseTitle( $pageName );
		return Title::newFromLinkTarget( $titleValue );
	}
}
