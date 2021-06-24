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
use TranslatablePage;
use TranslateUtils;

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
			'move-subpages',
			'Move subpages under the current page'
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
		$moveSubpages = $this->hasOption( 'move-subpages' );

		$userFactory = $mwService->getUserFactory();
		$user = $userFactory->newFromName( $username );

		if ( $user === null || !$user->isRegistered() ) {
			$this->fatalError( "User $username does not exist." );
		}

		$outputMsg = "Check if '$currentPagename' can be moved to '$newPagename'";
		$subpageMsg = '(excluding subpages)';
		if ( $moveSubpages ) {
			$subpageMsg = '(including subpages)';
		}

		$this->output( "$outputMsg $subpageMsg\n" );

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
		$blockers = $this->pageMover->checkMoveBlockers(
			$currentTitle,
			$newTitle,
			$user,
			$reason,
			$moveSubpages
		);

		if ( count( $blockers ) ) {
			$fatalErrorMsg = $this->parseErrorMessage( $blockers );
			$this->fatalError( $fatalErrorMsg );
		}

		$groupedPagesToMove = $this->getGroupedPagesToMove( $currentTitle );
		$this->displayPagesToMove( $currentTitle, $newTitle, $groupedPagesToMove );

		$haveConfirmation = $this->getConfirmation();
		if ( !$haveConfirmation ) {
			$this->output( "Exiting...\n" );
			return;
		}

		$this->output( "Starting page move\n" );

		$pagesToMove = $this->pageMover->getPagesToMove( $currentTitle, $newTitle, $moveSubpages );

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

	/** @return Title[][] */
	private function getGroupedPagesToMove( Title $source ): array {
		$page = TranslatablePage::newFromTitle( $source );

		$types = [
			'pt-movepage-list-pages' => [ $source ],
			'pt-movepage-list-translation' => $page->getTranslationPages(),
			'pt-movepage-list-section' => $page->getTranslationUnitPages( 'all' ),
			'pt-movepage-list-translatable' => $this->pageMover->getTranslatableSubpages( $page )
		];

		if ( TranslateUtils::allowsSubpages( $source ) ) {
			$types[ 'pt-movepage-list-other'] = $this->pageMover->getNormalSubpages( $page );
		}

		return $types;
	}

	private function displayPagesToMove( Title $currentTitle, Title $newTitle, array $pagesToMove ): void {
		$infoMessage = "\nThe following pages will be moved:\n";
		$count = 0;
		$subpagesCount = 0;
		$base = $currentTitle->getPrefixedText();

		foreach ( $pagesToMove as $type => $pages ) {
			$infoMessage .= $this->getSeparator();
			$pageCount = count( $pages );
			$infoMessage .= $this->message( $type )->numParams( $pageCount )->text() . "\n\n";
			if ( !$pageCount ) {
				$infoMessage .= $this->message( 'pt-movepage-list-no-pages' )->text() . "\n";
				continue;
			}

			$lines = [];
			if ( $type === 'pt-movepage-list-translatable' ) {
				$infoMessage .= $this->message( 'pt-movepage-list-translatable-note' )->text() . "\n";

				foreach ( $pages as $currentPage ) {
					$lines[] = '* ' . $currentPage->getPrefixedText();
				}
			} else {
				foreach ( $pages as $currentPage ) {
					$count++;

					if ( $type === 'pt-movepage-list-other' ) {
						$subpagesCount++;
					}

					$to = $this->pageMover->newPageTitle( $base, $currentPage, $newTitle );
					$lines[] = '* ' . $currentPage->getPrefixedText() . ' → ' . $to;
				}
			}

			$infoMessage .= implode( "\n", $lines ) . "\n";
		}

		$this->output( $infoMessage );

		$this->logSeparator();
		$this->output(
			$this->message( 'pt-movepage-list-count' )
				->numParams( $count, $subpagesCount )
				->text() . "\n"
		);
		$this->logSeparator();
		$this->output( "\n" );
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
