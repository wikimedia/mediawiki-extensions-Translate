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
class SpecialManageTranslatorSandbox extends SpecialPage {
	/** @var TranslationStashStorage */
	protected $stash;

	public function __construct() {
		global $wgTranslateUseSandbox;
		parent::__construct(
			'ManageTranslatorSandbox',
			'translate-sandboxmanage',
			$wgTranslateUseSandbox
		);
	}

	public function doesWrites() {
		return true;
	}

	protected function getGroupName() {
		return 'users';
	}

	public function execute( $params ) {
		$this->setHeaders();
		$this->checkPermissions();
		$out = $this->getOutput();
		$out->addModuleStyles( array( 'mediawiki.ui.button', 'jquery.uls.grid' ) );
		$out->addModuleStyles( 'ext.translate.special.managetranslatorsandbox.styles' );
		$out->addModules( 'ext.translate.special.managetranslatorsandbox' );
		$this->stash = new TranslationStashStorage( wfGetDB( DB_MASTER ) );

		$this->prepareForTests();
		$this->showPage();
	}

	/**
	 * Deletes a user page if it exists.
	 * This is needed especially when deleting sandbox users
	 * that were created as part of the integration tests.
	 * @param User $user
	 */
	protected function deleteUserPage( $user ) {
		$userpage = WikiPage::factory( $user->getUserPage() );
		if ( $userpage->exists() ) {
			$dummyError = '';
			$userpage->doDeleteArticleReal(
				wfMessage( 'tsb-delete-userpage-summary' )->inContentLanguage()->text(),
				false,
				0,
				true,
				$dummyError,
				$this->getUser()
			);
		}
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
			// Empty all the users, even if they were created manually
			// to ensure the number of users is what the tests expect
			$this->emptySandbox();

			$textUsernamePrefixes = array( 'Pupu', 'Orava' );
			$testLanguages = array( 'fi', 'uk', 'nl', 'he', 'bn' );
			$testLanguagesCount = count( $testLanguages );

			foreach ( $textUsernamePrefixes as $prefix ) {
				for ( $i = 0; $i < $testLanguagesCount; $i++ ) {
					$name = "$prefix$i";

					// Get rid of users, even if promoted during tests
					$userToDelete = User::newFromName( $name, false );
					$this->deleteUserPage( $userToDelete );
					TranslateSandbox::deleteUser( $userToDelete, 'force' );

					$user = TranslateSandbox::addUser( $name, "$name@blackhole.io", 'porkkana' );
					$user->setOption(
						'translate-sandbox',
						FormatJson::encode( array(
							'languages' => array( $testLanguages[$i] ),
							'comment' => '',
						) )
					);

					$reminders = array();
					for ( $reminderIndex = 0; $reminderIndex < $i; $reminderIndex++ ) {
						$reminders[] = wfTimestamp() - $reminderIndex * $i * 10000;
					}

					$user->setOption(
						'translate-sandbox-reminders',
						implode( '|', $reminders )
					);
					$user->saveSettings();

					for ( $j = 0; $j < $i; $j++ ) {
						$title = Title::makeTitle(
							NS_MEDIAWIKI,
							wfRandomString( 24 ) . '/' . $testLanguages[$i]
						);
						$translation = 'plop';
						$stashedTranslation = new StashedTranslation( $user, $title, $translation );
						$this->stash->addTranslation( $stashedTranslation );
					}
				}
			}

			// Another account for testing a translator to multiple languages
			$oldPolyglotUser = User::newFromName( 'Kissa', false );
			$this->deleteUserPage( $oldPolyglotUser );
			TranslateSandbox::deleteUser( $oldPolyglotUser, 'force' );

			$polyglotUser = TranslateSandbox::addUser( 'Kissa', 'kissa@blackhole.io', 'porkkana' );
			$polyglotUser->setOption(
				'translate-sandbox',
				FormatJson::encode( array(
					'languages' => $testLanguages,
					'comment' => "I know some languages, and I'm a developer.",
				) )
			);
			$polyglotUser->saveSettings();
			for ( $polyglotLang = 0; $polyglotLang < $testLanguagesCount; $polyglotLang++ ) {
				$title = Title::makeTitle(
					NS_MEDIAWIKI,
					wfRandomString( 24 ) . '/' . $testLanguages[$polyglotLang]
				);
				$translation = "plop in $testLanguages[$polyglotLang]";
				$stashedTranslation = new StashedTranslation( $polyglotUser, $title, $translation );
				$this->stash->addTranslation( $stashedTranslation );
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
		$out->addHTML( <<<HTML
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

		/** @var User $user */
		foreach ( $users as $user ) {
			$reminders = $user->getOption( 'translate-sandbox-reminders' );
			$reminders = $reminders ? explode( '|', $reminders ) : array();
			$remindersCount = count( $reminders );
			if ( $remindersCount ) {
				$lastReminderTimestamp = new MWTimestamp( end( $reminders ) );
				$lastReminderAgo = htmlspecialchars(
					$lastReminderTimestamp->getHumanTimestamp()
				);
			} else {
				$lastReminderAgo = '';
			}

			$requests[] = array(
				'username' => $user->getName(),
				'email' => $user->getEmail(),
				'gender' => $user->getOption( 'gender' ),
				'registrationdate' => $user->getRegistration(),
				'translations' => count( $this->stash->getTranslations( $user ) ),
				'languagepreferences' => FormatJson::decode( $user->getOption( 'translate-sandbox' ) ),
				'userid' => $user->getId(),
				'reminderscount' => $remindersCount,
				'lastreminder' => $lastReminderAgo,
			);
		}

		// Sort the requests based on translations and registration date
		usort( $requests, array( __CLASS__, 'translatorRequestSort' ) );

		foreach ( $requests as $request ) {
			$items[] = $this->makeRequestItem( $request );
		}

		$requestsList = implode( "\n", $items );

		return <<<HTML
<div class="row request-header">
	<div class="four columns">
		<button class="language-selector unselected">
			{$this->msg( 'tsb-all-languages-button-label' )->escaped()}
		</button>
	</div>
	<div class="five columns request-count"></div>
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
		$nameEncForId = htmlspecialchars( Sanitizer::escapeId( $request['username'], 'noninitial' ) );
		$emailEnc = htmlspecialchars( $request['email'] );
		$countEnc = htmlspecialchars( $request['translations'] );
		$timestamp = new MWTimestamp( $request['registrationdate'] );
		$agoEnc = htmlspecialchars( $timestamp->getHumanTimestamp() );

		return <<<HTML
<div class="row request" data-data="$requestdataEnc" id="tsb-request-$nameEncForId">
	<div class="two columns amount">
		<div class="translation-count">$countEnc</div>
	</div>
	<div class="seven columns request-info">
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
	 * @since 2013.12
	 * @param array $a Translation request
	 * @param array $b Translation request
	 * @return int comparison result
	 */
	public static function translatorRequestSort( $a, $b ) {
		$translationCountDiff = $b['translations'] - $a['translations'];
		if ( $translationCountDiff !== 0 ) {
			return $translationCountDiff;
		}

		$registrationDateDiff = $b['registrationdate'] - $a['registrationdate'];
		if ( $registrationDateDiff !== 0 ) {
			return $registrationDateDiff;
		}

		return strcmp( $a['username'], $b['username'] );
	}
}
