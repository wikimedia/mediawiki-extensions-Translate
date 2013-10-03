<?php
/**
 * Contains logic for special page ...
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Special page for managing sandboxed users.
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialTranslateSandbox extends SpecialPage {
	function __construct() {
		global $wgTranslateUseSandbox;
		parent::__construct( 'TranslateSandbox', 'translate-sandboxmanage', $wgTranslateUseSandbox );
	}

	public function execute( $params ) {
		$this->setHeaders();
		$this->checkPermissions();
		$out = $this->getOutput();
		$out->addModules( 'ext.translate.special.translatesandbox' );
		$this->showPage();
	}

	/**
	 * Generates the whole page html and appends it to output
	 */
	protected function showPage() {
		// Easier to do this way than in JS
		$token = Html::hidden( 'token', ApiTranslateSandbox::getToken(), array( 'id' => 'token' ) );

		$out = $this->getOutput();
		$out->addHtml( <<<HTML
<div class="grid">
	<div class="row">
		<div class="four columns pane filter">{$this->makeFilter()}</div>
		<div class="eight columns pane search"></div>
	</div>
	<div class="row">
		<div class="four columns pane requests">{$this->makeList()}</div>
		<div class="eight columns pane details"></div>
	</div>
	$token
</div>
HTML
		);
	}

	protected function makeFilter() {
		return $this->msg( 'tsb-filter-pending' )->escaped();
	}

	protected function makeList() {
		$items = array();

		$users = TranslateSandbox::getUsers();
		foreach ( $users as $user ) {
			$items[] = $this->makeRequestItem( $user );
		}

		return "\n\n" . implode( "\n", $items ) . "\n\n";
	}

	protected function makeRequestItem( User $user ) {
		$request = array(
			'username' => $user->getName(),
			'email' => $user->getEmail(),
			'registrationdate' => $user->getRegistration(),
			'translations' => 0,
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
	<div class="three columns amount">
		<div class="proofread-marker"></div>
		<div class="translation-count">$countEnc</div>
	</div>
	<div class="six columns details">
		<div class="row username">$nameEnc</div>
		<div class="row email">$emailEnc</div>
	</div>
	<div class="three columns approval">
		<div class="row selector"></div>
		<div class="row signup-age">$agoEnc</div>
	</div>
</div>
HTML;
	}
}
