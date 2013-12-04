<?php
/**
 * Contains logic for Special:ManageTranslatorSandbox
 *
 * @file
 * @author Niklas Laxström
 * @author Amir E. Aharoni
 * @license GPL-2.0+
 */

/**
 * Special page for managing sandboxed users.
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialTranslateSandbox extends SpecialPage {
	///< @param TranslationStashStorage
	protected $stash;

	function __construct() {
		global $wgTranslateUseSandbox;
		parent::__construct( 'TranslateSandbox', 'translate-sandboxmanage', $wgTranslateUseSandbox );
	}

	public function execute( $params ) {
		$this->setHeaders();
		$this->checkPermissions();
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.translatesandbox' );
		$this->stash = new TranslationStashStorage( wfGetDB( DB_MASTER ) );

		$this->prepareForTests();
		$this->showPage();
	}

	/**
	 * To facilitate browsers tests.
	 */
	public function prepareForTests() {
		global $wgTranslateTestUsers;

		$user = $this->getUser();
		$request = $this->getRequest();

		if ( !in_array( $user->getName(), $wgTranslateTestUsers, true ) ) {
			return;
		}

		if ( $request->getVal( 'integrationtesting' ) === 'populate' ) {
			foreach ( array( 'Pupu', 'Orava' ) as $prefix ) {
				for ( $i = 0; $i < 5; $i++ ) {
					$user = TranslateSandbox::addUser( "$prefix$i", "$prefix$i@pupun.kolo",  'porkkana' );
					for( $j = 0; $j < $i; $j++ ) {
						$title = Title::makeTitle( NS_MEDIAWIKI, wfRandomString( 24 ) . '/fi' );
						$translation = 'plop';
						$stashedTranslation = new StashedTranslation( $user, $title, $translation );
						$this->stash->addTranslation( $stashedTranslation );
					}
				}
			}
		} elseif ( $request->getVal( 'integrationtesting' ) === 'empty' ) {
			$users = TranslateSandbox::getUsers();
			foreach ( $users as $user ) {
				TranslateSandbox::deleteUser( $user );
			}
		}
	}

	/**
	 * Generates the whole page html and appends it to output
	 */
	protected function showPage() {
		$out = $this->getOutput();
		$out->addHtml( <<<HTML
<div class="grid">
	<div class="row">
		<div class="nine columns pane filter">{$this->makeFilter()}</div>
		<div class="three columns pane search">{$this->makeSearchBox()}</div>
	</div>
	<div class="row tsb-body">
		<div class="four columns pane requests">
			{$this->makeList()}
			<div class="request-footer">
				{$this->msg( 'tsb-selected-count' )->numParams( 0 )->escaped()}
			</div>
		</div>
		<div class="eight columns pane details"></div>
	</div>
</div>
HTML
		);
	}

	protected function makeFilter() {
		return $this->msg( 'tsb-filter-pending' )->escaped();
	}

	protected function makeSearchBox() {
		return <<<HTML
<input class="request-filter-box right"
	placeholder="{$this->msg( 'tsb-search-requests' )->escaped()}" type="search">
</input>
HTML;
	}

	protected function makeList() {
		$items = array();

		$users = TranslateSandbox::getUsers();

		foreach ( $users as $user ) {
			$items[] = $this->makeRequestItem( $user );
		}

		$count = count( $items );
		$requestsList = implode( "\n", $items );

		return <<<HTML
<div class="row request-header">
	<div class="four columns">
		<button class="language-selector">
			{$this->msg( "tsb-all-languages-button-label" )->escaped()}
		</button>
	</div>
	<div class="five columns request-count">
		<div>
			{$this->msg( "tsb-request-count" )->numParams( $count )->parse()}
		</div>
	</div>
	<div class="three columns center">
		<input class="request-selector-all" name="request" type="checkbox" />
	</div>
</div>
<div class="requests-list">
	{$requestsList}
</div>
HTML;
	}

	protected function makeRequestItem( User $user ) {
		$request = array(
			'username' => $user->getName(),
			'email' => $user->getEmail(),
			'registrationdate' => $user->getRegistration(),
			'translations' => count( $this->stash->getTranslations( $user ) ),
			'languagepreferences' => FormatJson::decode( $user->getOption( 'translate-sandbox' ) ),
			'userid' => $user->getId(),
		);

		$requestdataEnc = htmlspecialchars( FormatJson::encode( $request ) );

		$nameEnc = htmlspecialchars( $request['username'] );
		$emailEnc = htmlspecialchars( $request['email'] );
		$countEnc = htmlspecialchars( $request['translations'] );
		$timestamp = new MWTimestamp( $request['registrationdate'] );
		$agoEnc = htmlspecialchars( $timestamp->getHumanTimestamp() );

		return <<<HTML
<div class="row request" data-data="$requestdataEnc">
	<div class="two columns amount">
		<div class="proofread-marker"></div>
		<div class="translation-count">$countEnc</div>
	</div>
	<div class="seven columns details">
		<div class="row username">$nameEnc</div>
		<div class="row email">$emailEnc</div>
	</div>
	<div class="three columns approval center">
		<input class="row request-selector" name="request" type="checkbox" />
		<div class="row signup-age">$agoEnc</div>
	</div>
</div>
HTML;
	}
}
