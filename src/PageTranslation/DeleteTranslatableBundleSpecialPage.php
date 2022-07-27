<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use BagOStuff;
use ErrorPageError;
use HTMLForm;
use JobQueueGroup;
use MediaWiki\Extension\Translate\MessageBundleTranslation\MessageBundle;
use MediaWiki\Extension\Translate\MessageGroupProcessing\DeleteTranslatableBundleJob;
use MediaWiki\Extension\Translate\MessageGroupProcessing\SubpageListBuilder;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundle;
use MediaWiki\Extension\Translate\MessageGroupProcessing\TranslatableBundleFactory;
use MediaWiki\Permissions\PermissionManager;
use OutputPage;
use PermissionsError;
use ReadOnlyError;
use SpecialPage;
use Title;
use TranslateUtils;
use WebRequest;
use Xml;

/**
 * Special page which enables deleting translations of translatable bundles and translation pages
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage PageTranslation
 */
class DeleteTranslatableBundleSpecialPage extends SpecialPage {
	// Basic form parameters both as text and as titles
	private $text;
	/** @var Title */
	private $title;
	// Other form parameters
	/// There must be reason for everything.
	private $reason;
	/// Allow skipping non-translation subpages.
	private $doSubpages = false;
	/// Contains the language code if we are working with translation page
	private $code;
	/** @var BagOStuff */
	private $mainCache;
	/** @var PermissionManager */
	private $permissionManager;
	/** @var TranslatableBundleFactory */
	private $bundleFactory;
	/** @var SubpageListBuilder */
	private $subpageBuilder;
	/** @var JobQueueGroup */
	private $jobQueueGroup;
	/** @var ?string */
	private $entityType;
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
		BagOStuff $mainCache,
		PermissionManager $permissionManager,
		TranslatableBundleFactory $bundleFactory,
		SubpageListBuilder $subpageBuilder,
		JobQueueGroup $jobQueueGroup
	) {
		parent::__construct( 'PageTranslationDeletePage', 'pagetranslation' );
		$this->mainCache = $mainCache;
		$this->permissionManager = $permissionManager;
		$this->bundleFactory = $bundleFactory;
		$this->subpageBuilder = $subpageBuilder;
		$this->jobQueueGroup = $jobQueueGroup;
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

		if ( !$this->doBasicChecks() ) {
			return;
		}

		$out = $this->getOutput();

		// Real stuff starts here
		$this->entityType = $this->identifyEntityType();
		if ( !$this->entityType ) {
			throw new ErrorPageError( 'pt-deletepage-invalid-title', 'pt-deletepage-invalid-text' );
		}

		if ( $this->isTranslation() ) {
			[ , $this->code ] = TranslateUtils::figureMessage( $this->title->getText() );
		} else {
			$this->code = null;
		}

		$out->setPageTitle(
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
	 * @return bool
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

		$permissionErrors = $this->permissionManager->getPermissionErrors(
			'delete', $this->getUser(), $this->title
		);
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
	private function checkToken(): bool {
		return $this->getContext()->getCsrfTokenSet()->matchTokenField( 'wpEditToken' );
	}

	/** The query form. */
	private function showForm(): void {
		$this->getOutput()->addWikiMsg( 'pt-deletepage-intro', self::LOG_PAGE[ $this->entityType ] );

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

		$out->addWikiMsg( 'pt-deletepage-intro', self::LOG_PAGE[ $this->entityType ] );

		$subpages = $this->getPagesForDeletion();

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

		if ( TranslateUtils::allowsSubpages( $this->title ) ) {
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
		$jobs = [];
		$target = $this->title;
		$base = $this->title->getPrefixedText();
		$isTranslation = $this->isTranslation();
		$subpageList = $this->getPagesForDeletion();
		$bundle = $this->getValidBundleFromTitle();
		$bundleType = get_class( $bundle );

		$user = $this->getUser();
		foreach ( $subpageList[ 'translationPages' ] as $old ) {
			$jobs[$old->getPrefixedText()] = DeleteTranslatableBundleJob::newJob(
				$old, $base, $bundleType, $isTranslation, $user, $this->reason
			);
		}

		foreach ( $subpageList[ 'translationUnitPages' ] as $old ) {
			$jobs[$old->getPrefixedText()] = DeleteTranslatableBundleJob::newJob(
				$old, $base, $bundleType, $isTranslation, $user, $this->reason
			);
		}

		if ( $this->doSubpages ) {
			foreach ( $subpageList[ 'normalSubpages' ] as $old ) {
				$jobs[$old->getPrefixedText()] = DeleteTranslatableBundleJob::newJob(
					$old, $base, $bundleType, $isTranslation, $user, $this->reason
				);
			}
		}

		if ( !$isTranslation ) {
			$jobs[$this->title->getPrefixedText()] = DeleteTranslatableBundleJob::newJob(
				$this->title, $base, $bundleType, $isTranslation, $user, $this->reason
			);
		}

		$this->jobQueueGroup->push( $jobs );

		$this->mainCache->set(
			$this->mainCache->makeKey( 'pt-base', $target->getPrefixedText() ),
			array_keys( $jobs ),
			6 * $this->mainCache::TTL_HOUR
		);

		if ( !$isTranslation ) {
			$this->bundleFactory->getStore( $bundle )->delete( $this->title );
		}

		$this->getOutput()->addWikiMsg( 'pt-deletepage-started', self::LOG_PAGE[ $this->entityType ] );
	}

	private function getCommonFormFields(): array {
		$dropdownOptions = $this->msg( 'deletereason-dropdown' )->inContentLanguage()->text();

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

	private function getPagesForDeletion(): array {
		if ( $this->isTranslation() ) {
			$resultSet = $this->subpageBuilder->getEmptyResultSet();

			[ $titleKey, ] = TranslateUtils::figureMessage( $this->title->getPrefixedDBkey() );
			$translatablePage = TranslatablePage::newFromTitle( Title::newFromText( $titleKey ) );

			$resultSet['translationPages'] = [ $this->title ];
			$resultSet['translationUnitPages'] = $translatablePage->getTranslationUnitPages( $this->code );
			return $resultSet;
		} else {
			$bundle = $this->bundleFactory->getValidBundle( $this->title );
			return $this->subpageBuilder->getSubpagesPerType( $bundle, false );
		}
	}

	private function getValidBundleFromTitle(): TranslatableBundle {
		$bundleTitle = $this->title;
		if ( $this->isTranslation() ) {
			[ $key, ] = TranslateUtils::figureMessage( $this->title->getPrefixedDBkey() );
			$bundleTitle = Title::newFromText( $key );
		}

		return $this->bundleFactory->getValidBundle( $bundleTitle );
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
