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
		<div class="nine columns pane filter">{$this->makeFilter()}</div>
		<div class="three columns pane search">{$this->makeSearchBox()}</div>
	</div>
	<div class="row">
		<div class="four columns pane requests">{$this->makeList()}</div>
		<div class="four columns pane details"></div>
	</div>
	$token
</div>
HTML
		);
	}

	protected function makeFilter() {
		return $this->msg( 'tsb-filter-pending' )->escaped();
	}

	protected function makeSearchBox() {
		return <<<HTML
<input class="request-filter-box right" placeholder="Search requests" type="search"></input>
HTML;
	}

	protected function makeList() {
		$items = array();

		$users = TranslateSandbox::getUsers();
		foreach ( $users as $user ) {
			$items[] = $this->makeRequestItem( $user );
		}
		$count = count( $items );
		$out = <<<HTML
<div class="row request-header">
	<div class="four columns">
		<button class="language-selector">All languages</button>
	</div>
	<div class="five columns request-count">
		<div>{$this->msg( "tsb-request-count", $count ) }</div>
	</div>
	<div class="three columns center">
		<input class="request-selector-all" name="request" type="checkbox"/>
	</div>
</div>
HTML;
		return $out. "\n\n" . implode( "\n", $items ) . "\n\n";
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
	<div class="two columns amount">
		<div class="proofread-marker"></div>
		<div class="translation-count">$countEnc</div>
	</div>
	<div class="seven columns details">
		<div class="row username">$nameEnc</div>
		<div class="row email">$emailEnc</div>
	</div>
	<div class="three columns approval center">
		<input class="row request-selector" name="request" type="checkbox"/>
		<div class="row signup-age">$agoEnc</div>
	</div>
</div>
HTML;
	}
}
