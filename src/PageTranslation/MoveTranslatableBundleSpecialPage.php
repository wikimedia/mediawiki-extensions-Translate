<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use CommentStore;
use ErrorPageError;
use Html;
use HTMLForm;
use InvalidArgumentException;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundle;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleFactory;
use MediaWiki\Permissions\PermissionManager;
use Message;
use OutputPage;
use PermissionsError;
use ReadOnlyError;
use SplObjectStorage;
use ThrottledError;
use Title;
use UnlistedSpecialPage;
use Wikimedia\ObjectFactory\ObjectFactory;

/**
 * Replacement for Special:Movepage to allow renaming a translatable bundle and
 * all pages associated with it.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage PageTranslation
 */
class MoveTranslatableBundleSpecialPage extends UnlistedSpecialPage {
	// Form parameters both as text and as titles
	/** @var string */
	private $oldText;
	/** @var string */
	private $reason;
	/** @var bool */
	private $moveTalkpages = true;
	/** @var bool */
	private $moveSubpages = true;
	// Dependencies
	/** @var ObjectFactory */
	private $objectFactory;
	/** @var TranslatableBundleMover */
	private $bundleMover;
	/** @var PermissionManager */
	private $permissionManager;
	/** @var TranslatableBundleFactory */
	private $bundleFactory;
	private $movePageSpec;
	// Other
	/** @var Title */
	private $oldTitle;

	public function __construct(
		ObjectFactory $objectFactory,
		PermissionManager $permissionManager,
		TranslatableBundleMover $bundleMover,
		TranslatableBundleFactory $bundleFactory,
		$movePageSpec
	) {
		parent::__construct( 'Movepage' );
		$this->objectFactory = $objectFactory;
		$this->permissionManager = $permissionManager;
		$this->bundleMover = $bundleMover;
		$this->bundleFactory = $bundleFactory;
		$this->movePageSpec = $movePageSpec;
	}

	public function doesWrites(): bool {
		return true;
	}

	protected function getGroupName(): string {
		return 'pagetools';
	}

	/** @inheritDoc */
	public function execute( $par ) {
		$request = $this->getRequest();
		$user = $this->getUser();
		$this->addHelpLink( 'Help:Extension:Translate/Move_translatable_page' );

		$this->oldText = $request->getText(
			'wpOldTitle',
			$request->getText( 'target', $par ?? '' )
		);
		$newText = $request->getText( 'wpNewTitle' );

		$this->oldTitle = Title::newFromText( $this->oldText );
		$newTitle = Title::newFromText( $newText );
		// Normalize input
		if ( $this->oldTitle ) {
			$this->oldText = $this->oldTitle->getPrefixedText();
		}

		$this->reason = $request->getText( 'reason' );

		// This will throw exceptions if there is an error.
		$this->doBasicChecks();

		// Real stuff starts here
		$bundle = $this->bundleFactory->getBundle( $this->oldTitle );
		if ( $bundle && $bundle->isMoveable() ) {
			$this->getOutput()->setPageTitle( $this->getSpecialPageTitle( $bundle ) );

			if ( !$user->isAllowed( 'pagetranslation' ) ) {
				throw new PermissionsError( 'pagetranslation' );
			}

			$subaction = $this->getSubactionFromRequest( $request->getText( 'subaction' ) );

			$isValidPostRequest = $this->checkToken() && $request->wasPosted();
			if ( $isValidPostRequest && $subaction === 'check' ) {
				try {
					$pageCollection = $this->bundleMover->getPageMoveCollection(
						$this->oldTitle,
						$newTitle,
						$user,
						$this->reason,
						$this->moveSubpages,
						$this->moveTalkpages
					);
				} catch ( ImpossiblePageMove $e ) {
					$this->showErrors( $e->getBlockers() );
					$this->showForm( $bundle );
					return;
				}

				$this->showConfirmation( $pageCollection, $bundle );
			} elseif ( $isValidPostRequest && $subaction === 'perform' ) {
				$this->moveSubpages = $request->getBool( 'subpages' );
				$this->moveTalkpages = $request->getBool( 'talkpages' );

				$this->bundleMover->moveAsynchronously(
					$this->oldTitle,
					$newTitle,
					$this->moveSubpages,
					$this->getUser(),
					$this->msg( 'pt-movepage-logreason', $this->oldTitle )->inContentLanguage()->text(),
					$this->moveTalkpages
				);
				$this->getOutput()->addWikiMsg(
					'pt-movepage-started',
					$this->getLogPageWikiLink( $this->bundleFactory->getValidBundle( $this->oldTitle ) )
				);
			} else {
				$this->showForm( $bundle );
			}
		} else {
			// Delegate... don't want to reimplement this
			$sp = $this->objectFactory->createObject( $this->movePageSpec );
			$sp->execute( $par );
		}
	}

	/**
	 * Do the basic checks whether moving is possible and whether
	 * the input looks anywhere near sane.
	 * @throws PermissionsError|ErrorPageError|ReadOnlyError|ThrottledError
	 */
	protected function doBasicChecks(): void {
		$this->checkReadOnly();

		if ( $this->oldTitle === null ) {
			throw new ErrorPageError( 'notargettitle', 'notargettext' );
		}

		if ( !$this->oldTitle->exists() ) {
			throw new ErrorPageError( 'nopagetitle', 'nopagetext' );
		}

		if ( $this->getUser()->pingLimiter( 'move' ) ) {
			throw new ThrottledError;
		}

		// Check rights
		$permErrors = $this->permissionManager
			->getPermissionErrors( 'move', $this->getUser(), $this->oldTitle );
		if ( count( $permErrors ) ) {
			throw new PermissionsError( 'move', $permErrors );
		}
	}

	/** Checks token to protect against CSRF. */
	protected function checkToken(): bool {
		return $this->getContext()->getCsrfTokenSet()->matchTokenField( 'wpEditToken' );
	}

	/** Pretty-print the list of errors. */
	protected function showErrors( SplObjectStorage $errors ): void {
		// If there are many errors, for performance reasons we must parse them all at once
		$s = '';
		$context = 'pt-movepage-error-placeholder';
		foreach ( $errors as $title ) {
			$titleText = $title->getPrefixedText();
			$s .= "'''$titleText'''\n\n";
			$s .= $errors[ $title ]->getWikiText( false, $context );
		}

		$out = $this->getOutput();
		$out->addHTML(
			Html::errorBox(
				$out->msg(
					'pt-movepage-blockers',
					$this->getLanguage()->formatNum( count( $errors ) )
				)->parseAsBlock() .
				$out->parseAsContent( $s )
			)
		);
	}

	/** The query form. */
	public function showForm( TranslatableBundle $bundle ) {
		$this->getOutput()->addWikiMsg(
			'pt-movepage-intro',
			$this->getLogPageWikiLink(
				$this->bundleFactory->getBundle( Title::newFromText( $this->oldText ) )
			)
		);

		HTMLForm::factory( 'ooui', $this->getCommonFormFields(), $this->getContext() )
			->setMethod( 'post' )
			->setAction( $this->getPageTitle( $this->oldText )->getLocalURL() )
			->setSubmitName( 'subaction' )
			->setSubmitTextMsg( 'pt-movepage-action-check' )
			->setWrapperLegendMsg(
				$bundle instanceof MessageBundle ? 'pt-movepage-messagebundle-legend' : 'pt-movepage-legend'
			)
			->prepareForm()
			->displayForm( false );
	}

	/**
	 * The second form, which still allows changing some things.
	 * Lists all the action which would take place.
	 */
	protected function showConfirmation( PageMoveCollection $pageCollection, TranslatableBundle $bundle ): void {
		$out = $this->getOutput();

		$out->addWikiMsg(
			'pt-movepage-intro',
			$this->getLogPageWikiLink(
				$this->bundleFactory->getBundle( Title::newFromText( $this->oldText ) )
			)
		);

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
			$this->addSectionHeaderAndMessage( $out, $type, $pages );

			if ( !$pages ) {
				continue;
			}

			$lines = [];

			foreach ( $pages as $pagePairs ) {
				$count++;

				if ( $type === 'pt-movepage-list-other' ) {
					$subpagesCount++;
				}

				$old = $pagePairs->getOldTitle();
				$new = $pagePairs->getNewTitle();
				$line = '* ' . $old->getPrefixedText() . ' → ' . $new->getPrefixedText();
				if ( $pagePairs->hasTalkpage() ) {
					$count++;
					$talkpagesCount++;
					$line .= ' ' . $this->msg( 'pt-movepage-talkpage-exists' )->text();
				}

				$lines[] = $line;
			}

			$out->addWikiTextAsInterface( implode( "\n", $lines ) );
		}

		$translatableSubpages = $pageCollection->getTranslatableSubpages();
		$sectionType = 'pt-movepage-list-translatable';
		$this->addSectionHeaderAndMessage( $out, $sectionType, $translatableSubpages );
		if ( $translatableSubpages ) {
			$lines = [];
			$out->wrapWikiMsg( "'''$1'''", $this->msg( 'pt-movepage-list-translatable-note' ) );
			foreach ( $translatableSubpages as $page ) {
				$lines[] = '* ' . $page->getPrefixedText();
			}
			$out->addWikiTextAsInterface( implode( "\n", $lines ) );
		}

		$out->addWikiTextAsInterface( "----\n" );
		$out->addWikiMsg(
			'pt-movepage-list-count',
			$this->getLanguage()->formatNum( $count ),
			$this->getLanguage()->formatNum( $subpagesCount ),
			$this->getLanguage()->formatNum( $talkpagesCount )
		);

		$formDescriptor = array_merge(
			$this->getCommonFormFields(),
			[
				'subpages' => [
					'type' => 'check',
					'name' => 'subpages',
					'id' => 'mw-subpages',
					'label-message' => 'pt-movepage-subpages',
					'default' => $this->moveSubpages,
				],
				'talkpages' => [
					'type' => 'check',
					'name' => 'talkpages',
					'id' => 'mw-talkpages',
					'label-message' => 'pt-movepage-talkpages',
					'default' => $this->moveTalkpages
				]
			]
		);

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );
		$htmlForm
			->addButton( [
				'name' => 'subaction',
				'value' => $this->msg( 'pt-movepage-action-other' )->text(),
			] )
			->setMethod( 'post' )
			->setAction( $this->getPageTitle( $this->oldText )->getLocalURL() )
			->setSubmitName( 'subaction' )
			->setSubmitTextMsg( 'pt-movepage-action-perform' )
			->setWrapperLegendMsg(
				$bundle instanceof MessageBundle ? 'pt-movepage-messagebundle-legend' : 'pt-movepage-legend'
			)
			->prepareForm()
			->displayForm( false );
	}

	/** Add section header and no page message if there are no pages */
	private function addSectionHeaderAndMessage( OutputPage $out, string $type, array $pages ): void {
		$pageCount = count( $pages );
		$out->wrapWikiMsg( '=== $1 ===', [ $type, $pageCount ] );

		if ( !$pageCount ) {
			$out->addWikiMsg( 'pt-movepage-list-no-pages' );
		}
	}

	private function getSubactionFromRequest( string $subactionText ): string {
		switch ( $subactionText ) {
			case $this->msg( 'pt-movepage-action-check' )->text():
				return 'check';
			case $this->msg( 'pt-movepage-action-perform' )->text():
				return 'perform';
			case $this->msg( 'pt-movepage-action-other' )->text():
				return 'show-form';
			default:
				return 'show-form';
		}
	}

	private function getCommonFormFields(): array {
		return [
			'wpOldTitle' => [
				'type' => 'text',
				'name' => 'wpOldTitle',
				'label-message' => 'pt-movepage-current',
				'default' => $this->oldText,
				'readonly' => true,
			],
			'wpNewTitle' => [
				'type' => 'text',
				'name' => 'wpNewTitle',
				'label-message' => 'pt-movepage-new',
			],
			'reason' => [
				'type' => 'text',
				'name' => 'reason',
				'label-message' => 'pt-movepage-reason',
				'maxlength' => CommentStore::COMMENT_CHARACTER_LIMIT,
				'default' => $this->reason,
			],
			'subpages' => [
				'type' => 'hidden',
				'name' => 'subpages',
				'default' => $this->moveSubpages,
			],
			'talkpages' => [
				'type' => 'hidden',
				'name' => 'talkpages',
				'default' => $this->moveTalkpages
			]
		];
	}

	private function getSpecialPageTitle( TranslatableBundle $bundle ): Message {
		if ( $bundle instanceof TranslatablePage ) {
			return $this->msg( 'pt-movepage-title', $this->oldText );
		} elseif ( $bundle instanceof MessageBundle ) {
			return $this->msg( 'pt-movepage-messagebundle-title', $this->oldText );
		}

		throw new InvalidArgumentException( 'TranslatableBundle is neither a TranslatablePage or MessageBundle' );
	}

	private function getLogPageWikiLink( ?TranslatableBundle $bundle ): string {
		if ( $bundle instanceof MessageBundle ) {
			return 'Special:Log/messagebundle';
		}

		// Default to page translation log in case of errors
		return 'Special:Log/pagetranslation';
	}
}
