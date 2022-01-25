<?php
/**
 * Special page which enables deleting translations of translatable pages
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/**
 * Special page which enables deleting translations of translatable pages
 *
 * @ingroup SpecialPage PageTranslation
 */
class SpecialPageTranslationDeletePage extends SpecialPage {
	// Basic form parameters both as text and as titles
	protected $text;
	/** @var Title */
	protected $title;
	// Other form parameters
	/// 'check' or 'perform'
	protected $subaction;
	/// There must be reason for everything.
	protected $reason;
	/// Allow skipping non-translation subpages.
	protected $doSubpages = false;
	/** @var TranslatablePage */
	protected $page;
	/// Contains the language code if we are working with translation page
	protected $code;
	/** @var Title[] */
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
		$this->addhelpLink( 'Help:Deletion_and_undeletion' );

		$request = $this->getRequest();

		$par = (string)$par;

		// Yes, the use of getVal() and getText() is wanted, see bug T22365
		$this->text = $request->getVal( 'wpTitle', $par );
		$this->title = Title::newFromText( $this->text );
		$this->reason = $this->getDeleteReason( $request );
		$this->doSubpages = $request->getBool( 'subpages' );

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

		$permissionErrors = MediaWikiServices::getInstance()->getPermissionManager()
			->getPermissionErrors( 'delete', $this->getUser(), $this->title );
		if ( count( $permissionErrors ) ) {
			throw new PermissionsError( 'delete', $permissionErrors );
		}

		# Check for database lock
		$this->checkReadOnly();

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

		$formDescriptor = $this->getCommonFormFields();

		HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() )
			->addHiddenField( 'wpEditToken', $this->getUser()->getEditToken() )
			->setMethod( 'post' )
			->setAction( $this->getPageTitle( $this->text )->getLocalURL() )
			->setSubmitName( 'subaction' )
			->setSubmitTextMsg( 'pt-deletepage-action-check' )
			->setWrapperLegendMsg( 'pt-deletepage-any-legend' )
			->prepareForm()
			->displayForm( false );
	}

	/**
	 * The second form, which still allows changing some things.
	 * Lists all the action which would take place.
	 */
	protected function showConfirmation() {
		$out = $this->getOutput();
		$count = 0;
		$subpageCount = 0;

		$out->addWikiMsg( 'pt-deletepage-intro' );

		$out->wrapWikiMsg( '== $1 ==', 'pt-deletepage-list-pages' );
		if ( !$this->singleLanguage() ) {
			$count++;
			$out->addWikiTextAsInterface(
				$this->getChangeLine( $this->title )
			);
		}

		$out->wrapWikiMsg( '=== $1 ===', 'pt-deletepage-list-translation' );
		$translationPages = $this->getTranslationPages();
		$lines = [];
		foreach ( $translationPages as $old ) {
			$count++;
			$lines[] = $this->getChangeLine( $old );
		}
		$this->listPages( $out, $lines );

		$out->wrapWikiMsg( '=== $1 ===', 'pt-deletepage-list-section' );
		$sectionPages = $this->getSectionPages();
		$lines = [];
		foreach ( $sectionPages as $old ) {
			$count++;
			$lines[] = $this->getChangeLine( $old );
		}
		$this->listPages( $out, $lines );

		if ( TranslateUtils::allowsSubpages( $this->title ) ) {
			$out->wrapWikiMsg( '=== $1 ===', 'pt-deletepage-list-other' );
			$subpages = $this->getSubpages();
			$lines = [];
			foreach ( $subpages as $old ) {
				if ( TranslatablePage::isTranslationPage( $old ) ) {
					continue;
				}

				$subpageCount++;
				$lines[] = $this->getChangeLine( $old );
			}
			$this->listPages( $out, $lines );
		}

		$totalPageCount = $count + $subpageCount;

		$out->addWikiTextAsInterface( "----\n" );
		$out->addWikiMsg(
			'pt-deletepage-list-count',
			$this->getLanguage()->formatNum( $totalPageCount ),
			$this->getLanguage()->formatNum( $subpageCount )
		);

		$formDescriptor = $this->getCommonFormFields();
		$formDescriptor['subpages'] = [
			'type' => 'check',
			'name' => 'subpages',
			'id' => 'mw-subpages',
			'label' => $this->msg( 'pt-deletepage-subpages' )->text(),
			'default' => $this->doSubpages,
		];

		$htmlForm = HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() );

		if ( $this->singleLanguage() ) {
			$htmlForm->setWrapperLegendMsg( 'pt-deletepage-lang-legend' );
		} else {
			$htmlForm->setWrapperLegendMsg( 'pt-deletepage-full-legend' );
		}

		$htmlForm
			->addHiddenField( 'wpEditToken', $this->getUser()->getEditToken() )
			->setMethod( 'post' )
			->setAction( $this->getPageTitle( $this->text )->getLocalURL() )
			->setSubmitTextMsg( 'pt-deletepage-action-perform' )
			->setSubmitName( 'subaction' )
			->addButton( [
				'name' => 'subaction',
				'value' => $this->msg( 'pt-deletepage-action-other' )->text(),
			] )
			->prepareForm()
			->displayForm( false );
	}

	/**
	 * @param Title $title
	 * @return string One line of wikitext, without trailing newline.
	 */
	protected function getChangeLine( $title ) {
		return '* ' . $title->getPrefixedText();
	}

	protected function performAction() {
		$jobs = [];
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

		if ( $this->doSubpages ) {
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

		if ( !$this->singleLanguage() ) {
			$jobs[$this->title->getPrefixedText()] = TranslateDeleteJob::newJob(
				$this->title,
				$base,
				!$this->singleLanguage(),
				$user,
				$this->reason
			);
		}

		TranslateUtils::getJobQueueGroup()->push( $jobs );

		$cache = ObjectCache::getInstance( CACHE_DB );
		$cache->set(
			$cache->makeKey( 'pt-base', $target->getPrefixedText() ),
			array_keys( $jobs ),
			6 * $cache::TTL_HOUR
		);

		if ( !$this->singleLanguage() ) {
			$this->page->unmarkTranslatablePage();
			$this->clearMetadata();
		}

		MessageGroups::singleton()->recache();
		MessageIndexRebuildJob::newJob()->insertIntoJobQueue();

		$this->getOutput()->addWikiMsg( 'pt-deletepage-started' );
	}

	protected function clearMetadata() {
		// remove the entries from metadata table.
		$groupId = $this->page->getMessageGroupId();
		foreach ( TranslatablePage::METADATA_KEYS as $type ) {
			TranslateMetadata::set( $groupId, $type, false );
		}
		// remove the page from aggregate groups, if present in any of them.
		$aggregateGroups = MessageGroups::getGroupsByType( AggregateMessageGroup::class );
		TranslateMetadata::preloadGroups( array_keys( $aggregateGroups ), __METHOD__ );
		foreach ( $aggregateGroups as $group ) {
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

	/**
	 * Returns all section pages, including those which are currently not active.
	 * @return Title[]
	 */
	protected function getSectionPages() {
		$code = $this->singleLanguage() ? $this->code : false;

		return $this->page->getTranslationUnitPages( 'all', $code );
	}

	/**
	 * Returns only translation subpages.
	 * @return Title[]
	 */
	protected function getTranslationPages() {
		if ( $this->singleLanguage() ) {
			return [ $this->title ];
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

	/** @return bool */
	protected function singleLanguage() {
		return $this->code !== '';
	}

	protected function getCommonFormFields() {
		$dropdownOptions = $this->msg( 'deletereason-dropdown' )->inContentLanguage()->plain();

		$options = Xml::listDropDownOptions(
			$dropdownOptions,
			[
				'other' => $this->msg( 'pt-deletepage-reason-other' )->inContentLanguage()->text()
			]
		);

		return [
			'wpTitle' => [
				'type' => 'text',
				'name' => 'wpTitle',
				'label-message' => 'pt-deletepage-current',
				'size' => 30,
				'default' => $this->text,
				'readonly' => true,
			],
			'wpDeleteReasonList' => [
				'type' => 'select',
				'name' => 'wpDeleteReasonList',
				'label-message' => 'pt-deletepage-reason',
				'options' => $options,
			],
			'wpReason' => [
				'type' => 'text',
				'name' => 'wpReason',
				'label-message' => 'pt-deletepage-reason-details',
				'default' => $this->reason,
			]
		];
	}

	protected function listPages( OutputPage $out, array $lines ): void {
		if ( $lines ) {
			$out->addWikiTextAsInterface( implode( "\n", $lines ) );
		} else {
			$out->addWikiMsg( 'pt-deletepage-list-no-pages' );
		}
	}

	/**
	 * @param WebRequest $request
	 * @return string
	 */
	private function getDeleteReason( WebRequest $request ): string {
		$dropdownSelection = $request->getText( 'wpDeleteReasonList', 'other' );
		$reasonInput = $request->getText( 'wpReason' );

		if ( $dropdownSelection === 'other' ) {
			return $reasonInput;
		} elseif ( $reasonInput !== '' ) {
			// Entry from drop down menu + additional comment
			$separator = $this->msg( 'colon-separator' )->inContentLanguage()->text();
			return "$dropdownSelection$separator$reasonInput";
		} else {
			return $dropdownSelection;
		}
	}
}
