<?php
/**
 * Contains class to override Special:MovePage for page translation.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\PageTranslation\TranslatablePageMover;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\MediaWikiServices;

/**
 * Overrides Special:Movepage to to allow renaming a page translation page and
 * all related translations and derivative pages.
 *
 * @ingroup SpecialPage PageTranslation
 */
class SpecialPageTranslationMovePage extends MovePageForm {
	// Basic form parameters both as text and as titles
	protected $newText, $oldText;
	// Other form parameters
	/**
	 * 'check' or 'perform'
	 */
	protected $subaction;
	/** @var TranslatablePage instance. */
	protected $page;
	/**
	 * Whether MovePageForm extends SpecialPage
	 */
	protected $old;
	/** @var Title[] Cached list of translation pages. Not yet loaded if null. */
	protected $translationPages;
	/** @var Title[] Cached list of section pages. Not yet loaded if null. */
	protected $sectionPages;
	/** @var TranslatablePageMover */
	protected $pageMover;

	public function __construct() {
		parent::__construct();
		$this->pageMover = Services::getInstance()->getTranslatablePageMover();
	}

	/**
	 * Partially copies from SpecialMovepage.php, because it cannot be
	 * extended in other ways.
	 *
	 * @param string|null $par null if subpage not provided, string otherwise
	 * @throws PermissionsError
	 */
	public function execute( $par ) {
		$request = $this->getRequest();
		$user = $this->getUser();
		$this->addHelpLink( 'Help:Extension:Translate/Move_translatable_page' );

		// Yes, the use of getVal() and getText() is wanted, see bug T22365
		$this->oldText = $request->getVal( 'wpOldTitle', $request->getVal( 'target', $par ) );
		$this->newText = $request->getText( 'wpNewTitle' );

		$this->oldTitle = Title::newFromText( $this->oldText ?? '' );
		$this->newTitle = Title::newFromText( $this->newText );

		$this->reason = $request->getText( 'reason' );
		// Checkboxes that default being checked are tricky
		$this->moveSubpages = $request->getBool( 'subpages', !$request->wasPosted() );

		// This will throw exceptions if there is an error.
		$this->doBasicChecks();

		// Real stuff starts here
		$page = TranslatablePage::newFromTitle( $this->oldTitle );
		if ( $page->getMarkedTag() !== false ) {
			$this->page = $page;

			$this->getOutput()->setPageTitle( $this->msg( 'pt-movepage-title', $this->oldText ) );

			if ( !$user->isAllowed( 'pagetranslation' ) ) {
				throw new PermissionsError( 'pagetranslation' );
			}

			// Is there really no better way to do this?
			$subactionText = $request->getText( 'subaction' );
			switch ( $subactionText ) {
				case $this->msg( 'pt-movepage-action-check' )->text():
					$subaction = 'check';
					break;
				case $this->msg( 'pt-movepage-action-perform' )->text():
					$subaction = 'perform';
					break;
				case $this->msg( 'pt-movepage-action-other' )->text():
					$subaction = '';
					break;
				default:
					$subaction = '';
			}

			if ( $subaction === 'check' && $this->checkToken() && $request->wasPosted() ) {
				$blockers = $this->pageMover->checkMoveBlockers(
					$this->oldTitle,
					$this->newTitle,
					$user,
					$this->reason,
					$this->moveSubpages
				);
				if ( count( $blockers ) ) {
					$this->showErrors( $blockers );
					$this->showForm( [] );
				} else {
					$this->showConfirmation();
				}
			} elseif ( $subaction === 'perform' && $this->checkToken() && $request->wasPosted() ) {
				$this->pageMover->moveAsynchronously(
					$this->oldTitle,
					$this->newTitle,
					$this->moveSubpages,
					$this->getUser(),
					$this->msg( 'pt-movepage-logreason', $this->oldTitle )->inContentLanguage()->text()
				);
				$this->getOutput()->addWikiMsg( 'pt-movepage-started' );
			} else {
				$this->showForm( [] );
			}
		} else {
			// Delegate... don't want to reimplement this
			$sp = new MovePageForm();
			$sp->execute( $par );
		}
	}

	/**
	 * Do the basic checks whether moving is possible and whether
	 * the input looks anywhere near sane.
	 * @throws PermissionsError|ErrorPageError|ReadOnlyError|ThrottledError
	 */
	protected function doBasicChecks() {
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
		$permErrors = MediaWikiServices::getInstance()->getPermissionManager()
			->getPermissionErrors( 'move', $this->getUser(), $this->oldTitle );
		if ( count( $permErrors ) ) {
			throw new PermissionsError( 'move', $permErrors );
		}
	}

	/**
	 * Checks token. Use before real actions happen. Have to use wpEditToken
	 * for compatibility for SpecialMovepage.php.
	 *
	 * @return bool
	 */
	protected function checkToken() {
		return $this->getUser()->matchEditToken( $this->getRequest()->getVal( 'wpEditToken' ) );
	}

	/**
	 * Pretty-print the list of errors.
	 * @param SplObjectStorage $errors Array with message key and parameters
	 */
	protected function showErrors( SplObjectStorage $errors ): void {
		$out = $this->getOutput();

		$out->addHTML( Html::openElement( 'div', [ 'class' => 'errorbox' ] ) );
		$out->addWikiMsg(
			'pt-movepage-blockers',
			$this->getLanguage()->formatNum( count( $errors ) )
		);

		// If there are many errors, for performance reasons we must parse them all at once
		$s = '';
		$context = 'pt-movepage-error-placeholder';
		foreach ( $errors as $title ) {
			$titleText = $title->getPrefixedText();
			$s .= "'''$titleText'''\n\n";
			$s .= $errors[ $title ]->getWikiText( false, $context );
		}

		$out->addWikiTextAsInterface( $s );
		$out->addHTML( Html::closeElement( 'div' ) );
	}

	/**
	 * The query form.
	 *
	 * @param array $err Unused.
	 * @param bool $isPermError Unused.
	 */
	public function showForm( $err, $isPermError = false ) {
		$this->getOutput()->addWikiMsg( 'pt-movepage-intro' );

		$formDescriptor = [
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
				'default' => $this->newText,
			],
			'reason' => [
				'type' => 'text',
				'name' => 'reason',
				'label-message' => 'pt-movepage-reason',
				'maxlength' => CommentStore::COMMENT_CHARACTER_LIMIT,
				'default' => $this->reason,
			]
		];

		HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() )
			->setMethod( 'post' )
			->setAction( $this->getPageTitle( $this->oldText )->getLocalURL() )
			->setSubmitName( 'subaction' )
			->setSubmitTextMsg( 'pt-movepage-action-check' )
			->setWrapperLegendMsg( 'pt-movepage-legend' )
			->prepareForm()
			->displayForm( false );
	}

	/**
	 * The second form, which still allows changing some things.
	 * Lists all the action which would take place.
	 */
	protected function showConfirmation() {
		$out = $this->getOutput();

		$out->addWikiMsg( 'pt-movepage-intro' );

		$base = $this->oldTitle->getPrefixedText();
		$target = $this->newTitle;
		$count = 0;
		$subpagesCount = 0;

		$types = [
			'pt-movepage-list-pages' => [ $this->oldTitle ],
			'pt-movepage-list-translation' => $this->getTranslationPages(),
			'pt-movepage-list-section' => $this->getSectionPages(),
			'pt-movepage-list-translatable' => $this->pageMover->getTranslatableSubpages( $this->page )
		];

		if ( TranslateUtils::allowsSubpages( $this->oldTitle ) ) {
			$types[ 'pt-movepage-list-other'] = $this->pageMover->getNormalSubpages( $this->page );
		}

		foreach ( $types as $type => $pages ) {
			$pageCount = count( $pages );
			$out->wrapWikiMsg( '=== $1 ===', [ $type, $pageCount ] );

			if ( !$pageCount ) {
				$out->addWikiMsg( 'pt-movepage-list-no-pages' );
				continue;
			}

			if ( $type === 'pt-movepage-list-translatable' ) {
				$out->wrapWikiMsg(
					"'''$1'''", $this->msg( 'pt-movepage-list-translatable-note' )
				);
			}

			$lines = [];
			foreach ( $pages as $old ) {
				$canBeMoved = $type !== 'pt-movepage-list-translatable';
				if ( $canBeMoved ) {
					$count++;
				}

				if ( $type === 'pt-movepage-list-other' ) {
					$subpagesCount++;
				}

				$lines[] = $this->getChangeLine( $base, $old, $target, $canBeMoved );
			}

			$out->addWikiTextAsInterface( implode( "\n", $lines ) );
		}

		$out->addWikiTextAsInterface( "----\n" );
		$out->addWikiMsg(
			'pt-movepage-list-count',
			$this->getLanguage()->formatNum( $count ),
			$this->getLanguage()->formatNum( $subpagesCount )
		);

		$formDescriptor = [
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
				'default' => $this->newText,
				'readonly' => true,
			],
			'reason' => [
				'type' => 'text',
				'name' => 'reason',
				'label-message' => 'pt-movepage-reason',
				'maxlength' => CommentStore::COMMENT_CHARACTER_LIMIT,
				'default' => $this->reason,
			],
			'subpages' => [
				'type' => 'check',
				'name' => 'subpages',
				'id' => 'mw-subpages',
				'label-message' => 'pt-movepage-subpages',
				'default' => $this->moveSubpages,
			]
		];

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
			->setWrapperLegendMsg( 'pt-movepage-legend' )
			->prepareForm()
			->displayForm( false );
	}

	/**
	 * @param string $base
	 * @param Title $old
	 * @param Title $target
	 * @param bool $enabled
	 * @return string
	 */
	protected function getChangeLine( $base, Title $old, Title $target, $enabled = true ) {
		$to = $this->pageMover->newPageTitle( $base, $old, $target );

		if ( $enabled ) {
			return '* ' . $old->getPrefixedText() . ' → ' . $to;
		} else {
			return '* ' . $old->getPrefixedText();
		}
	}

	/**
	 * Returns all section pages, including those which are currently not active.
	 * @return Title[]
	 */
	protected function getSectionPages() {
		if ( !isset( $this->sectionPages ) ) {
			$this->sectionPages = $this->page->getTranslationUnitPages( 'all' );
		}

		return $this->sectionPages;
	}

	/**
	 * Returns only translation subpages.
	 * @return Title[]
	 */
	protected function getTranslationPages() {
		if ( !isset( $this->translationPages ) ) {
			$this->translationPages = $this->page->getTranslationPages();
		}

		return $this->translationPages;
	}
}
