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
	 * Add users to the sandbox or delete them to facilitate browsers tests.
	 * Use with caution!
	 */
	public function prepareForTests() {
		global $wgTranslateTestUsers;

		$user = $this->getUser();
		$request = $this->getRequest();

		if ( !in_array( $user->getName(), $wgTranslateTestUsers, true ) ) {
			return;
		}

		if ( $request->getVal( 'integrationtesting' ) === 'populate' ) {
			$textUsernamePrefixes = array( 'Orava', 'Pupu' );
			$testLanguages = array( 'fi', 'uk', 'nl', 'ml', 'bn' );
			$userCount = count( $testLanguages );

			foreach ( $textUsernamePrefixes as $prefix ) {
				for ( $i = 0; $i < $userCount; $i++ ) {
					$name = "$prefix$i";

					// Get rid of users, even if promoted during tests
					$userToDelete = User::newFromName( $name, false );
					TranslateSandbox::deleteUser( $userToDelete, 'force' );

					$user = TranslateSandbox::addUser( $name, "$prefix$i@pupun.kolo", 'porkkana' );
					$user->setOption(
						'translate-sandbox',
						FormatJson::encode( array(
							'languages' => array( $testLanguages[$i] ),
							'comment' => ''
						) )
					);
					$user->setOption( 'translate-sandbox-reminders', $i );
					$user->saveSettings();

					for ( $j = 0; $j < $i; $j++ ) {
						$title = Title::makeTitle( NS_MEDIAWIKI, wfRandomString( 24 ) . '/' . $testLanguages[$i] );
						$translation = 'plop';
						$stashedTranslation = new StashedTranslation( $user, $title, $translation );
						$this->stash->addTranslation( $stashedTranslation );
					}
				}
			}
		} elseif ( $request->getVal( 'integrationtesting' ) === 'empty' ) {
			$this->emptySandbox();
		}
	}

	/**
	 * Delete all the users in the sandbox.
	 * Use with caution!
	 * To facilitate browsers tests.
	 */
	protected function emptySandbox() {
		$users = TranslateSandbox::getUsers();
		foreach ( $users as $user ) {
			TranslateSandbox::deleteUser( $user );
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
				<span class="selected-counter">
					{$this->msg( 'tsb-selected-count' )->numParams( 0 )->escaped()}
				</span>
				&nbsp;
				<a href="#" class="older-requests-indicator"></a>
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
		$requests = array();
		$users = TranslateSandbox::getUsers();

		foreach ( $users as $user ) {
			$requests[] = array(
				'username' => $user->getName(),
				'email' => $user->getEmail(),
				'registrationdate' => $user->getRegistration(),
				'translations' => count( $this->stash->getTranslations( $user ) ),
				'languagepreferences' => FormatJson::decode( $user->getOption( 'translate-sandbox' ) ),
				'userid' => $user->getId(),
				'reminders' => (int) $user->getOption( 'translate-sandbox-reminders' ),
			);
		}

		// Sort the requests based on translations and registration date
		usort( $requests, array( __CLASS__, 'translatorRequestSort' ) );

		$count = count( $users );
		foreach ( $requests as $request ) {
			$items[] = $this->makeRequestItem( $request );
		}

		$requestsList = implode( "\n", $items );

		return <<<HTML
<div class="row request-header">
	<div class="four columns">
		<button class="language-selector unselected">
			{$this->msg( "tsb-all-languages-button-label" )->escaped()}
		</button>
		<button class="clear-language-selector hide">×</button>
	</div>
	<div class="five columns request-count">
		{$this->msg( "tsb-request-count" )->numParams( $count )->parse()}
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

	protected function makeRequestItem( $request ) {
		$requestdataEnc = htmlspecialchars( FormatJson::encode( $request ) );
		$nameEnc = htmlspecialchars( $request['username'] );
		$nameEncForId = htmlspecialchars( Sanitizer::escapeId( $request['username'] ) );
		$emailEnc = htmlspecialchars( $request['email'] );
		$countEnc = htmlspecialchars( $request['translations'] );
		$timestamp = new MWTimestamp( $request['registrationdate'] );
		$agoEnc = htmlspecialchars( $timestamp->getHumanTimestamp() );

		return <<<HTML
<div class="row request" data-data="$requestdataEnc" id="tsb-request-$nameEncForId">
	<div class="two columns amount">
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

	/**
	 * Sorts groups by descending order of number of translations,
	 * registration date and username
	 *
	 * @since 1.23
	 * @param array $a Translation request
	 * @param array $b Translation request
	 * @return int comparison result
	 */
	public static function translatorRequestSort( $a, $b ) {
		$translationCountDiff = $b['translations'] - $a['translations'];
		if ( $translationCountDiff !== 0 ) {
			return $translationCountDiff;
		}

		$registrationDateDiff = $a['registrationdate'] - $b['registrationdate'];
		if ( $registrationDateDiff !== 0 ) {
			return $registrationDateDiff;
		}

		return strcmp( $a['username'], $b['username'] );
	}
}
