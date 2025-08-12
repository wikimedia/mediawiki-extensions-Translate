<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use ErrorPageError;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundle;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleFactory;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Html\Html;
use MediaWiki\HTMLForm\HTMLForm;
use MediaWiki\Output\OutputPage;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Request\WebRequest;
use MediaWiki\SpecialPage\UnlistedSpecialPage;
use MediaWiki\Title\Title;
use PermissionsError;
use ReadOnlyError;

/**
 * Special page which enables deleting translations of translatable bundles and translation pages
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage PageTranslation
 */
class DeleteTranslatableBundleSpecialPage extends UnlistedSpecialPage {
	// Basic form parameters both as text and as titles
	private string $text;
	private ?Title $title;
	// Other form parameters
	/** There must be reason for everything. */
	private string $reason;
	/** Allow skipping non-translation subpages. */
	private bool $doSubpages = false;
	/** Contains the language code if we are working with translation page */
	private ?string $code;
	private PermissionManager $permissionManager;
	private TranslatableBundleDeleter $bundleDeleter;
	private TranslatableBundleFactory $bundleFactory;
	private string $entityType;
	private const PAGE_TITLE_MSG = [
		'messagebundle' => 'pt-deletepage-mb-title',
		'translatablepage' => 'pt-deletepage-tp-title',
		'translationpage' => 'pt-deletepage-lang-title'
	];
	private const WRAPPER_LEGEND_MSG = [
		'messagebundle' => 'pt-deletepage-mb-legend',
		'translatablepage' => 'pt-deletepage-tp-title',
		'translationpage' => 'pt-deletepage-tp-legend'
	];
	private const LOG_PAGE = [
		'messagebundle' => 'Special:Log/messagebundle',
		'translatablepage' => 'Special:Log/pagetranslation',
		'translationpage' => 'Special:Log/pagetranslation'
	];

	public function __construct(
		PermissionManager $permissionManager,
		TranslatableBundleDeleter $bundleDeleter,
		TranslatableBundleFactory $bundleFactory
	) {
		parent::__construct( 'PageTranslationDeletePage', 'pagetranslation' );
		$this->permissionManager = $permissionManager;
		$this->bundleFactory = $bundleFactory;
		$this->bundleDeleter = $bundleDeleter;
	}

	/** @inheritDoc */
	public function doesWrites() {
		return true;
	}

	/** @inheritDoc */
	public function execute( $par ) {
		$this->addHelpLink( 'Help:Deletion_and_undeletion' );

		$request = $this->getRequest();

		$par = (string)$par;

		// Yes, the use of getVal() and getText() is wanted, see bug T22365
		$this->text = $request->getVal( 'wpTitle', $par );
		$this->title = Title::newFromText( $this->text );
		$this->reason = $this->getDeleteReason( $request );
		$this->doSubpages = $request->getBool( 'subpages' );

		if ( !$this->doBasicChecks() ) {
			return;
		}

		$out = $this->getOutput();

		// Real stuff starts here
		$entityType = $this->identifyEntityType();
		if ( !$entityType ) {
			throw new ErrorPageError( 'pt-deletepage-invalid-title', 'pt-deletepage-invalid-text' );
		}
		$this->entityType = $entityType;

		if ( $this->isTranslation() ) {
			[ , $this->code ] = Utilities::figureMessage( $this->title->getText() );
		} else {
			$this->code = null;
		}

		$out->setPageTitleMsg(
			$this->msg( self::PAGE_TITLE_MSG[ $this->entityType ], $this->title->getPrefixedText() )
		);

		if ( !$this->getUser()->isAllowed( 'pagetranslation' ) ) {
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
	 */
	private function doBasicChecks(): bool {
		// Check rights
		if ( !$this->userCanExecute( $this->getUser() ) ) {
			$this->displayRestrictionError();
		}

		if ( $this->title === null ) {
			throw new ErrorPageError( 'notargettitle', 'notargettext' );
		}

		if ( !$this->title->exists() ) {
			throw new ErrorPageError( 'nopagetitle', 'nopagetext' );
		}

		$permissionStatus = $this->permissionManager->getPermissionStatus(
			'delete', $this->getUser(), $this->title
		);
		if ( !$permissionStatus->isOK() ) {
			throw new PermissionsError( 'delete', $permissionStatus );
		}

		# Check for database lock
		$this->checkReadOnly();

		// Let the caller know it's safe to continue
		return true;
	}

	/**
	 * Checks token. Use before real actions happen. Have to use wpEditToken
	 * for compatibility for SpecialMovepage.php.
	 */
	private function checkToken(): bool {
		return $this->getContext()->getCsrfTokenSet()->matchTokenField( 'wpEditToken' );
	}

	/** The query form. */
	private function showForm(): void {
		$out = $this->getOutput();
		$out->addBacklinkSubtitle( $this->title );
		$out->addWikiMsg( 'pt-deletepage-intro', self::LOG_PAGE[ $this->entityType ] );

		$formDescriptor = $this->getCommonFormFields();

		HTMLForm::factory( 'ooui', $formDescriptor, $this->getContext() )
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
	private function showConfirmation(): void {
		$out = $this->getOutput();
		$count = 0;
		$subpageCount = 0;

		$out->addBacklinkSubtitle( $this->title );
		$out->addWikiMsg( 'pt-deletepage-intro', self::LOG_PAGE[ $this->entityType ] );

		$subpages = $this->bundleDeleter->getPagesForDeletion( $this->title, $this->code, $this->isTranslation() );

		$out->wrapWikiMsg( '== $1 ==', 'pt-deletepage-list-pages' );

		if ( !$this->isTranslation() ) {
			$count++;
			$out->addWikiTextAsInterface(
				$this->getChangeLine( $this->title )
			);
		}

		$out->wrapWikiMsg( '=== $1 ===', 'pt-deletepage-list-translation' );
		$lines = [];
		foreach ( $subpages[ 'translationPages' ] as $old ) {
			$count++;
			$lines[] = $this->getChangeLine( $old );
		}
		$this->listPages( $out, $lines );

		$out->wrapWikiMsg( '=== $1 ===', 'pt-deletepage-list-section' );

		$lines = [];
		foreach ( $subpages[ 'translationUnitPages' ] as $old ) {
			$count++;
			$lines[] = $this->getChangeLine( $old );
		}
		$this->listPages( $out, $lines );

		if ( Utilities::allowsSubpages( $this->title ) ) {
			$out->wrapWikiMsg( '=== $1 ===', 'pt-deletepage-list-other' );
			$subpages = $subpages[ 'normalSubpages' ];
			$lines = [];
			foreach ( $subpages as $old ) {
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
		$htmlForm
			->setWrapperLegendMsg(
				$this->msg( self::WRAPPER_LEGEND_MSG[ $this->entityType ], $this->title->getPrefixedText() )
			)
			->setAction( $this->getPageTitle( $this->text )->getLocalURL() )
			->setSubmitTextMsg( 'pt-deletepage-action-perform' )
			->setSubmitName( 'subaction' )
			->setSubmitDestructive()
			->addButton( [
				'name' => 'subaction',
				'value' => $this->msg( 'pt-deletepage-action-other' )->text(),
			] )
			->prepareForm()
			->displayForm( false );
	}

	/** @return string One line of wikitext, without trailing newline. */
	private function getChangeLine( Title $title ): string {
		return '* ' . $title->getPrefixedText();
	}

	private function performAction(): void {
		$this->bundleDeleter->deleteAsynchronously(
			$this->title,
			$this->isTranslation(),
			$this->getUser(),
			$this->bundleDeleter->getPagesForDeletion( $this->title, $this->code, $this->isTranslation() ),
			$this->doSubpages,
			$this->reason,
			$this->getContext()->exportSession()
		);

		$this->getOutput()->addWikiMsg( 'pt-deletepage-started', self::LOG_PAGE[ $this->entityType ] );
	}

	private function getCommonFormFields(): array {
		$dropdownOptions = $this->msg( 'deletereason-dropdown' )->inContentLanguage()->text();

		$options = Html::listDropdownOptions(
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
				'default' => $this->title->getPrefixedText(),
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

	private function listPages( OutputPage $out, array $lines ): void {
		if ( $lines ) {
			$out->addWikiTextAsInterface( implode( "\n", $lines ) );
		} else {
			$out->addWikiMsg( 'pt-deletepage-list-no-pages' );
		}
	}

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

	/** Indentify type of entity being deleted: messagebundle, translatablepage, or translations */
	private function identifyEntityType(): ?string {
		$bundle = $this->bundleFactory->getBundle( $this->title );
		if ( $bundle ) {
			if ( $bundle instanceof MessageBundle ) {
				return 'messagebundle';
			} else {
				return 'translatablepage';
			}
		} elseif ( TranslatablePage::isTranslationPage( $this->title ) ) {
			return 'translationpage';
		}

		return null;
	}

	private function isTranslation(): bool {
		return $this->entityType === 'translationpage';
	}
}
