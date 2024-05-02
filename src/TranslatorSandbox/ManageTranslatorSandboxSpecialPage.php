<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Html\Html;
use MediaWiki\Json\FormatJson;
use MediaWiki\Parser\Sanitizer;
use MediaWiki\SpecialPage\SpecialPage;
use MediaWiki\User\Options\UserOptionsLookup;
use MediaWiki\User\User;
use MediaWiki\Utils\MWTimestamp;

/**
 * Special page for managing sandboxed users.
 *
 * @author Niklas Laxström
 * @author Amir E. Aharoni
 * @license GPL-2.0-or-later
 * @ingroup SpecialPage TranslateSpecialPage
 */
class ManageTranslatorSandboxSpecialPage extends SpecialPage {
	/** @var TranslationStashReader */
	private $stash;
	/** @var UserOptionsLookup */
	private $userOptionsLookup;
	private TranslateSandbox $translateSandbox;

	public const CONSTRUCTOR_OPTIONS = [
		'TranslateUseSandbox',
	];

	public function __construct(
		TranslationStashReader $stash,
		UserOptionsLookup $userOptionsLookup,
		TranslateSandbox $translateSandbox,
		ServiceOptions $options
	) {
		$this->stash = $stash;
		$this->userOptionsLookup = $userOptionsLookup;
		$this->translateSandbox = $translateSandbox;

		parent::__construct(
			'ManageTranslatorSandbox',
			'translate-sandboxmanage',
			$options->get( 'TranslateUseSandbox' )
		);
	}

	/** @inheritDoc */
	public function doesWrites() {
		return true;
	}

	protected function getGroupName(): string {
		return 'translation';
	}

	/** @inheritDoc */
	public function execute( $params ) {
		$this->setHeaders();
		$this->checkPermissions();
		$out = $this->getOutput();
		$out->addModuleStyles(
			[
				'ext.translate.special.managetranslatorsandbox.styles',
				'mediawiki.ui.button',
				'jquery.uls.grid',
				'mediawiki.codex.messagebox.styles',
			]
		);
		$out->addModules( 'ext.translate.special.managetranslatorsandbox' );

		$this->showPage();
	}

	/** Generates the whole page html and appends it to output */
	private function showPage(): void {
		$out = $this->getOutput();

		$nojs = Html::errorBox(
			$this->msg( 'tux-nojs' )->escaped(),
			'',
			'tux-nojs'
		);
		$out->addHTML( $nojs );

		$out->addHTML(
			<<<HTML
				<div class="grid tsb-container">
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
								\u{00A0}
								<a href="#" class="older-requests-indicator"></a>
							</div>
						</div>
						<div class="eight columns pane details"></div>
					</div>
				</div>
				HTML
		);
	}

	private function makeFilter(): string {
		return $this->msg( 'tsb-filter-pending' )->escaped();
	}

	private function makeSearchBox(): string {
		return <<<HTML
			<input class="request-filter-box right"
				placeholder="{$this->msg( 'tsb-search-requests' )->escaped()}" type="search" />
			HTML;
	}

	private function makeList(): string {
		$items = [];
		$requests = [];
		$users = $this->translateSandbox->getUsers();

		/** @var User $user */
		foreach ( $users as $user ) {
			$reminders = $this->userOptionsLookup->getOption( $user, 'translate-sandbox-reminders' );
			$reminders = $reminders ? explode( '|', $reminders ) : [];
			$remindersCount = count( $reminders );
			if ( $remindersCount ) {
				$lastReminderTimestamp = new MWTimestamp( end( $reminders ) );
				$lastReminderRfcTimestamp = $lastReminderTimestamp->getTimestamp( TS_RFC2822 );
				$lastReminderAgo = htmlspecialchars(
					$this->getHumanTimestamp( $lastReminderTimestamp )
				);
			} else {
				$lastReminderAgo = '';
				$lastReminderRfcTimestamp = '';
			}

			$requests[] = [
				'username' => $user->getName(),
				'email' => $user->getEmail(),
				'gender' => $this->userOptionsLookup->getOption( $user, 'gender' ),
				'registrationdate' => $user->getRegistration(),
				'translations' => count( $this->stash->getTranslations( $user ) ),
				'languagepreferences' => FormatJson::decode(
					$this->userOptionsLookup->getOption( $user, 'translate-sandbox' )
				),
				'userid' => $user->getId(),
				'reminderscount' => $remindersCount,
				'lastreminder' => $lastReminderAgo,
				'lastreminderts' => $lastReminderRfcTimestamp
			];
		}

		// Sort the requests based on translations and registration date
		usort( $requests, [ $this, 'translatorRequestSort' ] );

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
				<div class="three columns text-center">
					<input class="request-selector-all" name="request" type="checkbox" />
				</div>
			</div>
			<div class="requests-list">
				{$requestsList}
			</div>
			HTML;
	}

	private function makeRequestItem( array $request ): string {
		$requestdataEnc = htmlspecialchars( FormatJson::encode( $request ) );
		$nameEnc = htmlspecialchars( $request['username'] );
		$nameEncForId =
			htmlspecialchars(
				Sanitizer::escapeIdForAttribute( 'tsb-request-' . $request['username'] )
			);
		$emailEnc = htmlspecialchars( $request['email'] );
		$countEnc = htmlspecialchars( (string)$request['translations'] );
		$timestamp = new MWTimestamp( $request['registrationdate'] );
		$agoEnc = htmlspecialchars( $this->getHumanTimestamp( $timestamp ) );

		return <<<HTML
			<div class="row request" data-data="$requestdataEnc" id="$nameEncForId">
				<div class="two columns amount">
					<div class="translation-count">$countEnc</div>
				</div>
				<div class="seven columns request-info">
					<div class="row username">$nameEnc</div>
					<div class="row email" dir="ltr">$emailEnc</div>
				</div>
				<div class="three columns approval text-center">
					<input class="row request-selector" name="request" type="checkbox" />
					<div class="row signup-age">$agoEnc</div>
				</div>
			</div>
			HTML;
	}

	private function getHumanTimestamp( MWTimestamp $ts ): string {
		return $this->getLanguage()->getHumanTimestamp( $ts, null, $this->getUser() );
	}

	/**
	 * Sorts groups by descending order of number of translations,
	 * registration date and username
	 */
	private function translatorRequestSort( array $a, array $b ): int {
		return $b['translations'] <=> $a['translations']
			?: $b['registrationdate'] <=> $a['registrationdate']
				?: strnatcasecmp( $a['username'], $b['username'] );
	}
}
