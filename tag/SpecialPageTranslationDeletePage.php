<?php
/**
 * Special page which enables deleting translations of translatable pages
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Special page which enables deleting translations of translatable pages
 *
 * @ingroup SpecialPage PageTranslation
 */
class SpecialPageTranslationDeletePage extends SpecialPage {
	// Basic form parameters both as text and as titles
	protected $text;

	/**
	 * @var Title
	 */
	protected $title;

	// Other form parameters
	/// 'check' or 'perform'
	protected $subaction;

	/// There must be reason for everything.
	protected $reason;

	/// Allow skipping non-translation subpages.
	protected $doSubpages = false;

	/**
	 * @var TranslatablePage
	 */
	protected $page;

	/// Contains the language code if we are working with translation page
	protected $code;

	protected $sectionPages;

	protected $translationPages;

	public function __construct() {
		parent::__construct( 'PageTranslationDeletePage', 'pagetranslation' );
	}

	public function doesWrites() {
		return true;
	}

	public function isListed() {
		return false;
	}

	public function execute( $par ) {
		$request = $this->getRequest();

		$par = (string)$par;

		// Yes, the use of getVal() and getText() is wanted, see bug T22365
		$this->text = $request->getVal( 'wpTitle', $par );
		$this->title = Title::newFromText( $this->text );
		$this->reason = $request->getText( 'reason' );
		// Checkboxes that default being checked are tricky
		$this->doSubpages = $request->getBool( 'subpages', !$request->wasPosted() );

		$user = $this->getUser();

		if ( $this->doBasicChecks() !== true ) {
			return;
		}

		$out = $this->getOutput();

		// Real stuff starts here
		if ( TranslatablePage::isSourcePage( $this->title ) ) {
			$title = $this->msg( 'pt-deletepage-full-title', $this->title->getPrefixedText() );
			$out->setPageTitle( $title );

			$this->code = '';
			$this->page = TranslatablePage::newFromTitle( $this->title );
		} else {
			$page = TranslatablePage::isTranslationPage( $this->title );
			if ( $page ) {
				$title = $this->msg( 'pt-deletepage-lang-title', $this->title->getPrefixedText() );
				$out->setPageTitle( $title );

				list( , $this->code ) = TranslateUtils::figureMessage( $this->title->getText() );
				$this->page = $page;
			} else {
				throw new ErrorPageError(
					'pt-deletepage-invalid-title',
					'pt-deletepage-invalid-text'
				);
			}
		}

		if ( !$user->isAllowed( 'pagetranslation' ) ) {
			throw new PermissionsError( 'pagetranslation' );
		}

		// Is there really no better way to do this?
		$subactionText = $request->getText( 'subaction' );
		switch ( $subactionText ) {
			case $this->msg( 'pt-deletepage-action-check' )->text():
				$subaction = 'check';
				break;
			case $this->msg( 'pt-deletepage-action-perform' )->text():
				$subaction = 'perform';
				break;
			case $this->msg( 'pt-deletepage-action-other' )->text():
				$subaction = '';
				break;
			default:
				$subaction = '';
		}

		if ( $subaction === 'check' && $this->checkToken() && $request->wasPosted() ) {
			$this->showConfirmation();
		} elseif ( $subaction === 'perform' && $this->checkToken() && $request->wasPosted() ) {
			$this->performAction();
		} else {
			$this->showForm();
		}
	}

	/**
	 * Do the basic checks whether moving is possible and whether
	 * the input looks anywhere near sane.
	 * @throws PermissionsError|ErrorPageError|ReadOnlyError
	 * @return bool
	 */
	protected function doBasicChecks() {
		# Check rights
		if ( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
		}

		if ( $this->title === null ) {
			throw new ErrorPageError( 'notargettitle', 'notargettext' );
		}

		if ( !$this->title->exists() ) {
			throw new ErrorPageError( 'nopagetitle', 'nopagetext' );
		}

		$permissionErrors = $this->title->getUserPermissionsErrors( 'delete', $this->getUser() );
		if ( count( $permissionErrors ) ) {
			throw new PermissionsError( 'delete', $permissionErrors );
		}

		# Check for database lock
		if ( wfReadOnly() ) {
			throw new ReadOnlyError;
		}

		// Let the caller know it's safe to continue
		return true;
	}

	/**
	 * Checks token. Use before real actions happen. Have to use wpEditToken
	 * for compatibility for SpecialMovepage.php.
	 * @return bool
	 */
	protected function checkToken() {
		return $this->getUser()->matchEditToken( $this->getRequest()->getVal( 'wpEditToken' ) );
	}

	/**
	 * The query form.
	 */
	protected function showForm() {
		$this->getOutput()->addWikiMsg( 'pt-deletepage-intro' );

		$subaction = array( 'name' => 'subaction' );
		$formParams = array(
			'method' => 'post',
			'action' => $this->getPageTitle( $this->text )->getLocalURL()
		);

		$form = array();
		$form[] = Xml::fieldset( $this->msg( 'pt-deletepage-any-legend' )->text() );
		$form[] = Html::openElement( 'form', $formParams );
		$form[] = Html::hidden( 'wpEditToken', $this->getUser()->getEditToken() );
		$this->addInputLabel(
			$form,
			$this->msg( 'pt-deletepage-current' )->text(),
			'wpTitle',
			30,
			$this->text
		);
		$this->addInputLabel(
			$form,
			$this->msg( 'pt-deletepage-reason' )->text(),
			'reason',
			60,
			$this->reason
		);
		$form[] = Xml::submitButton(
			$this->msg( 'pt-deletepage-action-check' )->text(),
			$subaction
		);
		$form[] = Xml::closeElement( 'form' );
		$form[] = Xml::closeElement( 'fieldset' );
		$this->getOutput()->addHTML( implode( "\n", $form ) );
	}

	/**
	 * Shortcut for keeping the code at least a bit readable. Adds label and
	 * input into $form array.
	 *
	 * @param array $form \list{String} Array where input element and label is appended.
	 * @param string $label Label text.
	 * @param string $name Name attribute.
	 * @param int|bool $size Size attribute of the input element. Default false.
	 * @param string|bool $text Text of the value attribute. Default false.
	 * @param array $attribs Extra attributes. Default empty array.
	 */
	protected function addInputLabel( &$form, $label, $name, $size = false, $text = false,
		array $attribs = array()
	) {
		$br = Html::element( 'br' );
		list( $label, $input ) = Xml::inputLabelSep( $label, $name, $name, $size, $text, $attribs );
		$form[] = $label . $br;
		$form[] = $input . $br;
	}

	/**
	 * The second form, which still allows changing some things.
	 * Lists all the action which would take place.
	 */
	protected function showConfirmation() {
		$out = $this->getOutput();
		$count = 0;

		$out->addWikiMsg( 'pt-deletepage-intro' );

		$out->wrapWikiMsg( '== $1 ==', 'pt-deletepage-list-pages' );
		if ( !$this->singleLanguage() ) {
			$count++;
			$this->printChangeLine( $this->title );
		}

		$out->wrapWikiMsg( '=== $1 ===', 'pt-deletepage-list-translation' );
		$translationPages = $this->getTranslationPages();
		foreach ( $translationPages as $old ) {
			$count++;
			$this->printChangeLine( $old );
		}

		$out->wrapWikiMsg( '=== $1 ===', 'pt-deletepage-list-section' );
		$sectionPages = $this->getSectionPages();
		foreach ( $sectionPages as $old ) {
			$count++;
			$this->printChangeLine( $old );
		}

		$out->wrapWikiMsg( '=== $1 ===', 'pt-deletepage-list-other' );
		$subpages = $this->getSubpages();
		foreach ( $subpages as $old ) {
			if ( TranslatablePage::isTranslationPage( $old ) ) {
				continue;
			}

			if ( $this->doSubpages ) {
				$count++;
			}

			$this->printChangeLine( $old, $this->doSubpages );
		}

		$out->addWikiText( "----\n" );
		$out->addWikiMsg( 'pt-deletepage-list-count', $this->getLanguage()->formatNum( $count ) );

		$br = Html::element( 'br' );
		$readonly = array( 'readonly' => 'readonly' );

		$subaction = array( 'name' => 'subaction' );
		$formParams = array(
			'method' => 'post',
			'action' => $this->getPageTitle( $this->text )->getLocalURL()
		);

		$form = array();
		if ( $this->singleLanguage() ) {
			$form[] = Xml::fieldset( $this->msg( 'pt-deletepage-lang-legend' )->text() );
		} else {
			$form[] = Xml::fieldset( $this->msg( 'pt-deletepage-full-legend' )->text() );
		}
		$form[] = Html::openElement( 'form', $formParams );
		$form[] = Html::hidden( 'wpEditToken', $this->getUser()->getEditToken() );
		$this->addInputLabel(
			$form,
			$this->msg( 'pt-deletepage-current' )->text(),
			'wpTitle',
			30,
			$this->text, $readonly );
		$this->addInputLabel(
			$form,
			$this->msg( 'pt-deletepage-reason' )->text(),
			'reason',
			60,
			$this->reason );
		$form[] = Xml::checkLabel(
			$this->msg( 'pt-deletepage-subpages' )->text(),
			'subpages',
			'mw-subpages',
			$this->doSubpages,
			$readonly
		) . $br;
		$form[] = Xml::submitButton(
			$this->msg( 'pt-deletepage-action-perform' )->text(),
			$subaction
		);
		$form[] = Xml::submitButton(
			$this->msg( 'pt-deletepage-action-other' )->text(),
			$subaction
		);
		$form[] = Xml::closeElement( 'form' );
		$form[] = Xml::closeElement( 'fieldset' );
		$out->addHTML( implode( "\n", $form ) );
	}

	/**
	 * @param $title Title
	 * @param $enabled bool
	 */
	protected function printChangeLine( $title, $enabled = true ) {
		if ( $enabled ) {
			$this->getOutput()->addWikiText( '* ' . $title->getPrefixedText() );
		} else {
			$this->getOutput()->addWikiText( '* <s>' . $title->getPrefixedText() . '</s>' );
		}
	}

	protected function performAction() {
		$jobs = array();
		$target = $this->title;
		$base = $this->title->getPrefixedText();

		$translationPages = $this->getTranslationPages();
		$user = $this->getUser();
		foreach ( $translationPages as $old ) {
			$jobs[$old->getPrefixedText()] = TranslateDeleteJob::newJob(
				$old,
				$base,
				!$this->singleLanguage(),
				$user,
				$this->reason
			);
		}

		$sectionPages = $this->getSectionPages();
		foreach ( $sectionPages as $old ) {
			$jobs[$old->getPrefixedText()] = TranslateDeleteJob::newJob(
				$old,
				$base,
				!$this->singleLanguage(),
				$user,
				$this->reason
			);
		}

		if ( !$this->doSubpages ) {
			$subpages = $this->getSubpages();
			foreach ( $subpages as $old ) {
				if ( TranslatablePage::isTranslationPage( $old ) ) {
					continue;
				}

				$jobs[$old->getPrefixedText()] = TranslateDeleteJob::newJob(
					$old,
					$base,
					!$this->singleLanguage(),
					$user,
					$this->reason
				);
			}
		}

		JobQueueGroup::singleton()->push( $jobs );

		$cache = wfGetCache( CACHE_DB );
		$cache->set(
			wfMemcKey( 'pt-base', $target->getPrefixedText() ),
			array_keys( $jobs ),
			60 * 60 * 6
		);

		if ( !$this->singleLanguage() ) {
			$this->page->unmarkTranslatablePage();
		}

		$this->clearMetadata();
		MessageGroups::singleton()->recache();
		MessageIndexRebuildJob::newJob()->insert();

		$this->getOutput()->addWikiMsg( 'pt-deletepage-started' );
	}

	protected function clearMetadata() {
		// remove the entries from metadata table.
		$groupId = $this->page->getMessageGroupId();
		TranslateMetadata::set( $groupId, 'prioritylangs', false );
		TranslateMetadata::set( $groupId, 'priorityforce', false );
		TranslateMetadata::set( $groupId, 'priorityreason', false );
		// remove the page from aggregate groups, if present in any of them.
		$groups = MessageGroups::getAllGroups();
		foreach ( $groups as $group ) {
			if ( $group instanceof AggregateMessageGroup ) {
				$subgroups = TranslateMetadata::get( $group->getId(), 'subgroups' );
				if ( $subgroups !== false ) {
					$subgroups = explode( ',', $subgroups );
					$subgroups = array_flip( $subgroups );
					if ( isset( $subgroups[$groupId] ) ) {
						unset( $subgroups[$groupId] );
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

	/**
	 * Returns all section pages, including those which are currently not active.
	 * @return Array of titles.
	 */
	protected function getSectionPages() {
		$code = $this->singleLanguage() ? $this->code : false;

		return $this->page->getTranslationUnitPages( 'all', $code );
	}

	/**
	 * Returns only translation subpages.
	 * @return Array of titles.
	 */
	protected function getTranslationPages() {
		if ( $this->singleLanguage() ) {
			return array( $this->title );
		}

		if ( !isset( $this->translationPages ) ) {
			$this->translationPages = $this->page->getTranslationPages();
		}

		return $this->translationPages;
	}

	/**
	 * Returns all subpages, if the namespace has them enabled.
	 * @return array|TitleArray Empty array or TitleArray.
	 */
	protected function getSubpages() {
		return $this->title->getSubpages();
	}

	/**
	 * @return bool
	 */
	protected function singleLanguage() {
		return $this->code !== '';
	}
}
