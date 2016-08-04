<?php
/**
 * Contains class to override Special:MovePage for page translation.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Overrides Special:Movepage to to allow renaming a page translation page and
 * all related translations and derivative pages.
 *
 * @ingroup SpecialPage PageTranslation
 */
class SpecialPageTranslationMovePage extends MovePageForm {
	// Basic form parameters both as text and as titles
	protected $newText, $oldText;

	/**
	 * @var Title
	 */
	protected $newTitle, $oldTitle;

	// Other form parameters
	/**
	 * 'check' or 'perform'
	 */
	protected $subaction;

	/**
	 * There must be reason for everything.
	 */
	protected $reason;

	/**
	 * Allow skipping non-translation subpages.
	 */
	protected $moveSubpages;

	/**
	 * @var TranslatablePage instance.
	 */
	protected $page;

	/**
	 * Whether MovePageForm extends SpecialPage
	 */
	protected $old;

	/**
	 * @var Title[] Cached list of translation pages. Not yet loaded if null.
	 */
	protected $translationPages;

	/**
	 * @var Title[] Cached list of section pages. Not yet loaded if null.
	 */
	protected $sectionPages;

	public function __construct() {
		parent::__construct( 'Movepage' );
	}

	public function doesWrites() {
		return true;
	}

	public function isListed() {
		return false;
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

		$par = is_null( $par ) ? '' : $par; // Title::newFromText expects strings only

		// Yes, the use of getVal() and getText() is wanted, see bug T22365
		$this->oldText = $request->getVal( 'wpOldTitle', $request->getVal( 'target', $par ) );
		$this->newText = $request->getText( 'wpNewTitle' );

		$this->oldTitle = Title::newFromText( $this->oldText );
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
				$blockers = $this->checkMoveBlockers();
				if ( count( $blockers ) ) {
					$this->showErrors( $blockers );
					$this->showForm( array() );
				} else {
					$this->showConfirmation();
				}
			} elseif ( $subaction === 'perform' && $this->checkToken() && $request->wasPosted() ) {
				$this->performAction();
			} else {
				$this->showForm( array() );
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
		$permErrors = $this->oldTitle->getUserPermissionsErrors( 'move', $this->getUser() );
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
	 * @param array $errors Array with message key and parameters
	 */
	protected function showErrors( array $errors ) {
		if ( count( $errors ) ) {
			$out = $this->getOutput();

			$out->addHTML( Html::openElement( 'div', array( 'class' => 'error' ) ) );
			$out->addWikiMsg(
				'pt-movepage-blockers',
				$this->getLanguage()->formatNum( count( $errors ) )
			);
			$out->addHTML( '<ul>' );
			foreach ( $errors as $error ) {
				// I have no idea what the parser is doing, but this is mad.
				// <li>$1</li> doesn't work.
				$out->wrapWikiMsg( "<li>$1", $error );
			}
			$out->addHTML( '</ul></div>' );
		}
	}

	/**
	 * The query form.
	 *
	 * @param array $par
	 */
	public function showForm( $par ) {
		$this->getOutput()->addWikiMsg( 'pt-movepage-intro' );

		$br = Html::element( 'br' );
		$subaction = array( 'name' => 'subaction' );
		$readonly = array( 'readonly' => 'readonly' );
		$formParams = array(
			'method' => 'post',
			'action' => $this->getPageTitle( $this->oldText )->getLocalURL()
		);

		$form = array();
		$form[] = Xml::fieldset( $this->msg( 'pt-movepage-legend' )->text() );
		$form[] = Html::openElement( 'form', $formParams );
		$form[] = Html::hidden( 'wpEditToken', $this->getUser()->getEditToken() );
		$this->addInputLabel(
			$form,
			$this->msg( 'pt-movepage-current' )->text(),
			'wpOldTitle',
			30,
			$this->oldText,
			$readonly
		);
		$this->addInputLabel(
			$form,
			$this->msg( 'pt-movepage-new' )->text(),
			'wpNewTitle',
			30,
			$this->newText
		);
		$this->addInputLabel(
			$form,
			$this->msg( 'pt-movepage-reason' )->text(),
			'reason',
			45,
			$this->reason
		);
		$form[] = Xml::checkLabel(
			$this->msg( 'pt-movepage-subpages' )->text(),
			'subpages',
			'mw-subpages',
			$this->moveSubpages
		) . $br;
		$form[] = Xml::submitButton( $this->msg( 'pt-movepage-action-check' )->text(), $subaction );
		$form[] = Xml::closeElement( 'form' );
		$form[] = Xml::closeElement( 'fieldset' );
		$this->getOutput()->addHTML( implode( "\n", $form ) );
	}

	/**
	 * Shortcut for keeping the code at least a bit readable. Adds label and
	 * input into $form array.
	 *
	 * @param string[] $form Array where input element and label is appended.
	 * @param string $label Label text.
	 * @param string $name Name attribute.
	 * @param bool|int $size Size attribute of the input element. Default false.
	 * @param bool|string $text Text of the value attribute. Default false.
	 * @param array $attribs Extra attributes. Default empty array.
	 */
	protected function addInputLabel( &$form, $label, $name, $size = false, $text = false,
		array $attribs = array()
	) {
		$br = Html::element( 'br' );
		list( $label, $input ) = Xml::inputLabelSep(
			$label,
			$name,
			$name,
			$size,
			$text,
			$attribs
		);
		$form[] = $label . $br;
		$form[] = $input . $br;
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

		$types = array(
			'pt-movepage-list-pages' => array( $this->oldTitle ),
			'pt-movepage-list-translation' => $this->getTranslationPages(),
			'pt-movepage-list-section' => $this->getSectionPages(),
			'pt-movepage-list-other' => $this->getSubpages(),
		);

		foreach ( $types as $type => $pages ) {
			$out->wrapWikiMsg( '=== $1 ===', array( $type, count( $pages ) ) );

			$lines = array();
			foreach ( $pages as $old ) {
				$toBeMoved = true;

				// These pages need specific checks
				if ( $type === 'pt-movepage-list-other' ) {
					$toBeMoved = $this->moveSubpages;

					if ( TranslatablePage::isTranslationPage( $old ) ) {
						continue;
					}
				}

				if ( $toBeMoved ) {
					$count++;
				}

				$lines[] = $this->getChangeLine( $base, $old, $target, $toBeMoved );
			}

			$out->addWikiText( implode( "\n", $lines ) );
		}

		$out->addWikiText( "----\n" );
		$out->addWikiMsg( 'pt-movepage-list-count', $this->getLanguage()->formatNum( $count ) );

		$br = Html::element( 'br' );
		$readonly = array( 'readonly' => 'readonly' );
		$subaction = array( 'name' => 'subaction' );
		$formParams = array(
			'method' => 'post',
			'action' => $this->getPageTitle( $this->oldText )->getLocalURL()
		);

		$form = array();
		$form[] = Xml::fieldset( $this->msg( 'pt-movepage-legend' )->text() );
		$form[] = Html::openElement( 'form', $formParams );
		$form[] = Html::hidden( 'wpEditToken', $this->getUser()->getEditToken() );
		$this->addInputLabel(
			$form,
			$this->msg( 'pt-movepage-current' )->text(),
			'wpOldTitle',
			30,
			$this->oldText,
			$readonly
		);
		$this->addInputLabel(
			$form,
			$this->msg( 'pt-movepage-new' )->text(),
			'wpNewTitle',
			30,
			$this->newText,
			$readonly
		);
		$this->addInputLabel(
			$form,
			$this->msg( 'pt-movepage-reason' )->text(),
			'reason',
			60,
			$this->reason
		);
		$form[] = Html::hidden( 'subpages', $this->moveSubpages );
		$form[] = Xml::checkLabel(
			$this->msg( 'pt-movepage-subpages' )->text(),
			'subpagesFake',
			'mw-subpages',
			$this->moveSubpages,
			$readonly
		) . $br;
		$form[] = Xml::submitButton( $this->msg( 'pt-movepage-action-perform' )->text(), $subaction );
		$form[] = Xml::submitButton( $this->msg( 'pt-movepage-action-other' )->text(), $subaction );
		$form[] = Xml::closeElement( 'form' );
		$form[] = Xml::closeElement( 'fieldset' );
		$out->addHTML( implode( "\n", $form ) );
	}

	/**
	 * @param string $base
	 * @param Title $old
	 * @param Title $target
	 * @param bool $enabled
	 * @return string
	 */
	protected function getChangeLine( $base, Title $old, Title $target, $enabled = true ) {
		$to = $this->newPageTitle( $base, $old, $target );

		if ( $enabled ) {
			return '* ' . $old->getPrefixedText() . ' → ' . $to;
		} else {
			return '* ' . $old->getPrefixedText();
		}
	}

	protected function performAction() {
		$jobs = array();
		$user = $this->getUser();
		$target = $this->newTitle;
		$base = $this->oldTitle->getPrefixedText();
		$oldLatest = $this->oldTitle->getLatestRevID();

		$params = array(
			'base-source' => $this->oldTitle->getPrefixedText(),
			'base-target' => $this->newTitle->getPrefixedText(),
		);

		$translationPages = $this->getTranslationPages();
		foreach ( $translationPages as $old ) {
			$to = $this->newPageTitle( $base, $old, $target );
			$jobs[$old->getPrefixedText()] = TranslateMoveJob::newJob( $old, $to, $params, $user );
		}

		$sectionPages = $this->getSectionPages();
		foreach ( $sectionPages as $old ) {
			$to = $this->newPageTitle( $base, $old, $target );
			$jobs[$old->getPrefixedText()] = TranslateMoveJob::newJob( $old, $to, $params, $user );
		}

		if ( $this->moveSubpages ) {
			$subpages = $this->getSubpages();
			foreach ( $subpages as $old ) {
				if ( TranslatablePage::isTranslationPage( $old ) ) {
					continue;
				}

				$to = $this->newPageTitle( $base, $old, $target );
				$jobs[$old->getPrefixedText()] = TranslateMoveJob::newJob(
					$old,
					$to,
					$params,
					$user
				);
			}
		}

		// This is used by TranslateMoveJob
		wfGetCache( CACHE_ANYTHING )->set( wfMemcKey( 'translate-pt-move', $base ), count( $jobs ) );
		JobQueueGroup::singleton()->push( $jobs );

		TranslateMoveJob::forceRedirects( false );

		$errors = $this->oldTitle->moveTo( $this->newTitle, true, $this->reason, false );
		if ( is_array( $errors ) ) {
			$this->showErrors( $errors );
		}

		TranslateMoveJob::forceRedirects( true );

		$newTpage = TranslatablePage::newFromTitle( $this->newTitle );
		$newTpage->addReadyTag( $this->newTitle->getLatestRevID( Title::GAID_FOR_UPDATE ) );

		if ( $newTpage->getMarkedTag() === $oldLatest ) {
			$newTpage->addMarkedTag( $this->newTitle->getLatestRevID( Title::GAID_FOR_UPDATE ) );
		}

		// remove the entries from metadata table.
		$oldGroupId = $this->page->getMessageGroupId();
		$newGroupId = $newTpage->getMessageGroupId();
		$this->moveMetadata( $oldGroupId, $newGroupId );

		MessageGroups::singleton()->recache();
		MessageIndexRebuildJob::newJob()->insert();

		$this->getOutput()->addWikiMsg( 'pt-movepage-started' );
	}

	protected function moveMetadata( $oldGroupId, $newGroupId ) {
		$prioritylangs = TranslateMetadata::get( $oldGroupId, 'prioritylangs' );
		$priorityforce = TranslateMetadata::get( $oldGroupId, 'priorityforce' );
		$priorityreason = TranslateMetadata::get( $oldGroupId, 'priorityreason' );
		TranslateMetadata::set( $oldGroupId, 'prioritylangs', false );
		TranslateMetadata::set( $oldGroupId, 'priorityforce', false );
		TranslateMetadata::set( $oldGroupId, 'priorityreason', false );
		if ( $prioritylangs ) {
			TranslateMetadata::set( $newGroupId, 'prioritylangs', $prioritylangs );
		}
		if ( $priorityforce ) {
			TranslateMetadata::set( $newGroupId, 'priorityforce', $priorityforce );
		}
		if ( $priorityreason !== false ) {
			TranslateMetadata::set( $newGroupId, 'priorityreason', $priorityreason );
		}
		// make the changes in aggregate groups metadata, if present in any of them.
		$groups = MessageGroups::getAllGroups();
		foreach ( $groups as $group ) {
			if ( $group instanceof AggregateMessageGroup ) {
				$subgroups = TranslateMetadata::get( $group->getId(), 'subgroups' );
				if ( $subgroups !== false ) {
					$subgroups = explode( ',', $subgroups );
					$subgroups = array_flip( $subgroups );
					if ( isset( $subgroups[$oldGroupId] ) ) {
						$subgroups[$newGroupId] = $subgroups[$oldGroupId];
						unset( $subgroups[$oldGroupId] );
						$subgroups = array_flip( $subgroups );
						TranslateMetadata::set(
							$group->getId(),
							'subgroups',
							implode( ',', $subgroups )
						);
					}
				}
			}
		}
	}

	protected function checkMoveBlockers() {
		$blockers = array();

		$target = $this->newTitle;

		if ( !$target ) {
			$blockers[] = array( 'pt-movepage-block-base-invalid' );

			return $blockers;
		}

		if ( $target->inNamespaces( NS_MEDIAWIKI, NS_TRANSLATIONS ) ) {
			$blockers[] = array( 'immobile-target-namespace', $target->getNsText() );

			return $blockers;
		}

		$base = $this->oldTitle->getPrefixedText();

		if ( $target->exists() ) {
			$blockers[] = array( 'pt-movepage-block-base-exists', $target->getPrefixedText() );
		} else {
			$errors = $this->oldTitle->isValidMoveOperation( $target, true, $this->reason );
			if ( is_array( $errors ) ) {
				$blockers = array_merge( $blockers, $errors );
			}
		}

		// Don't spam the same errors for all pages if base page fails
		if ( $blockers ) {
			return $blockers;
		}

		// Collect all the old and new titles for checcks
		$titles = array();

		$pages = $this->getTranslationPages();
		foreach ( $pages as $old ) {
			$titles['tp'][] = array( $old, $this->newPageTitle( $base, $old, $target ) );
		}

		$pages = $this->getSectionPages();
		foreach ( $pages as $old ) {
			$titles['section'][] = array( $old, $this->newPageTitle( $base, $old, $target ) );
		}

		$subpages = array();
		if ( $this->moveSubpages ) {
			$subpages = $this->getSubpages();
		}
		foreach ( $subpages as $old ) {
			if ( !TranslatablePage::isTranslationPage( $old ) ) {
				$titles['subpage'][] = array( $old, $this->newPageTitle( $base, $old, $target ) );
			}
		}

		// Check that all new titles are valid
		$lb = new LinkBatch();
		foreach ( $titles as $type => $list ) {
			// Give grep a chance to find the usages:
			// pt-movepage-block-tp-invalid, pt-movepage-block-section-invalid,
			// pt-movepage-block-subpage-invalid
			foreach ( $list as $pair ) {
				list( $old, $new ) = $pair;
				if ( $new === null ) {
					$blockers[] = array(
						"pt-movepage-block-$type-invalid",
						$old->getPrefixedText()
					);
					continue;
				}
				$lb->addObj( $old );
				$lb->addObj( $new );
			}
		}

		if ( $blockers ) {
			return $blockers;
		}

		// Check that there are no move blockers
		$lb->execute();
		foreach ( $titles as $type => $list ) {
			// Give grep a chance to find the usages:
			// pt-movepage-block-tp-exists, pt-movepage-block-section-exists,
			// pt-movepage-block-subpage-exists
			foreach ( $list as $pair ) {
				list( $old, $new ) = $pair;
				if ( $new->exists() ) {
					$blockers[] = array(
						"pt-movepage-block-$type-exists",
						$old->getPrefixedText(),
						$new->getPrefixedText()
					);
				} else {
					/* This method has terrible performance:
					 * - 2 queries by core
					 * - 3 queries by lqt
					 * - and no obvious way to preload the data! */
					$errors = $old->isValidMoveOperation( $target, false );
					if ( is_array( $errors ) ) {
						$blockers = array_merge( $blockers, $errors );
					}

					/* Because of the above, check only one of the possibly thousands
					 * of section pages and assume rest are fine. */
					if ( $type === 'section' ) {
						break;
					}
				}
			}
		}

		return $blockers;
	}

	/**
	 * Makes old title into a new title by replacing $base part of old title
	 * with $target.
	 * @param string $base Title::getPrefixedText() of the base page.
	 * @param Title $old The title to convert.
	 * @param Title $target The target title for the base page.
	 * @return Title
	 */
	protected function newPageTitle( $base, Title $old, Title $target ) {
		$search = preg_quote( $base, '~' );

		if ( $old->inNamespace( NS_TRANSLATIONS ) ) {
			$new = $old->getText();
			$new = preg_replace( "~^$search~", $target->getPrefixedText(), $new, 1 );

			return Title::makeTitleSafe( NS_TRANSLATIONS, $new );
		} else {
			$new = $old->getPrefixedText();
			$new = preg_replace( "~^$search~", $target->getPrefixedText(), $new, 1 );

			return Title::newFromText( $new );
		}
	}

	/**
	 * Returns all section pages, including those which are currently not active.
	 * @return TitleArray.
	 */
	protected function getSectionPages() {
		if ( !isset( $this->sectionPages ) ) {
			$this->sectionPages = $this->page->getTranslationUnitPages( 'all' );
		}

		return $this->sectionPages;
	}

	/**
	 * Returns only translation subpages.
	 * @return Array of titles.
	 */
	protected function getTranslationPages() {
		if ( !isset( $this->translationPages ) ) {
			$this->translationPages = $this->page->getTranslationPages();
		}

		return $this->translationPages;
	}

	/**
	 * Returns all subpages, if the namespace has them enabled.
	 * @return mixed TitleArray, or empty array if this page's namespace doesn't allow subpages
	 */
	protected function getSubpages() {
		return $this->page->getTitle()->getSubpages();
	}
}
