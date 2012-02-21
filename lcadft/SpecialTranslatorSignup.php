<?php
/**
 * Form for translators to register contact methods
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Form for translators to register contact methods
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */

class SpecialTranslatorSignup extends SpecialPage {
	public function __construct() {
		parent::__construct( 'TranslatorSignup' );
	}

	public function execute( $parameters ) {
		if ( !$this->getUser()->isLoggedIn() ) {
			throw new PermissionsError( 'read' );
		}

		$context = $this->getContext();
		$htmlForm = new HtmlForm( $this->getDataModel(), $context, 'lcadft' );
		$htmlForm->setId( 'lcadft-form' );
		$htmlForm->setSubmitText( $context->msg( 'lcadft-submit' )->text() );
		$htmlForm->setSubmitID( 'lcadft-submit' );
		$htmlForm->setSubmitCallback( array( $this, 'formSubmit' ) );
		$htmlForm->show();

		$this->setHeaders();
		$this->getOutput()->addInlineScript(
<<<JAVASCRIPT
jQuery( function ( $ ) {
	var toggle = function () {
		$( '#mw-input-wpcmethod-talkpage-elsewhere-loc' ).toggle( $( '#mw-input-wpcmethod-talkpage-elsewhere' ).prop( 'checked' ) );
	};
	toggle();
	$( '#mw-input-wpcmethod-talkpage-elsewhere' ).change( toggle );
} );
JAVASCRIPT
		);
	}
	public function getDataModel() {
		global $wgLCADFTContactMethods;

		$m['username'] = array(
			'type' => 'info',
			'label-message' => 'lcadft-username',
			'default' => $this->getUser()->getName(),
			'section' => 'info',
		);

		$user = $this->getUser();
		if ( $user->isEmailConfirmed() ) {
			$status = $this->msg( 'lcadft-email-confirmed' )->parse();
		} elseif ( trim( $user->getEmail() ) !== '' )  {
			$submit = Xml::submitButton( $this->msg( 'confirmemail_send' )->text(), array( 'name' => 'x' ) );
			$status = $this->msg( 'lcadft-email-unconfirmed' )->rawParams( $submit )->parse();
		} else {
			$status = $this->msg( 'lcadft-email-notset' )->parse();
		}

		$m['emailstatus'] = array(
			'type' => 'info',
			'label-message' => 'lcadft-emailstatus',
			'default' => $status,
			'section' => 'info',
			'raw' => true,
		);

		foreach ( $wgLCADFTContactMethods as $method => $value ) {
			if ( $value === false ) {
				continue;
			}

			$m["cmethod-$method"] = array(
				'type' => 'check',
				'label-message' => "lcadft-cmethod-$method",
				'default' => $user->getOption( "lcadft-cmethod-$method" ),
				'section' => 'contact',
			);
			if ( $method === 'email' ) {
				$m["cmethod-$method"]['disabled'] = !$user-> isEmailConfirmed();
			}

			if ( $method === 'talkpage-elsewhere' ) {
				$m['cmethod-talkpage-elsewhere-loc'] = array(
					'type' => 'select',
					'default' => $user->getOption( 'lcadft-cmethod-talkpage-elsewhere-loc' ),
					'section' => 'contact',
					'options' => $this->getOtherWikis(),
				);
			}
		}

		$m['freq'] = array(
			'type' => 'radio',
			'default' => $user->getOption( 'lcadft-freq', 'always' ),
			'section' => 'frequency',
			'options' => array(
				$this->msg( 'lcadft-freq-always' )->text()  => 'always',
				$this->msg( 'lcadft-freq-week' )->text()    => 'week',
				$this->msg( 'lcadft-freq-month' )->text()   => 'month',
				$this->msg( 'lcadft-freq-weekly' )->text()  => 'weekly',
				$this->msg( 'lcadft-freq-monthly' )->text() => 'monthly',
			),
		);
		return $m;
	}

	public function formSubmit( $formData, $form ) {
		global $wgRequest;
		$user = $this->getUser();

		if ( $wgRequest->getVal( 'x' ) === $this->msg( 'confirmemail_send' )->text() ) {
			$user->sendConfirmationMail( 'set' );
			return;
		}

		foreach ( $formData as $key => $value ) {
			$user->setOption( "lcadft-$key", $value );
		}
		$user->saveSettings();
	}

	protected function getOtherWikis() {
		if ( !class_exists( 'CentralAuthUser' ) ) {
			return array();
		}
		$globalUser = new CentralAuthUser( $this->getUser()->getName() );
		if ( !$globalUser->exists() ) {
			return array();
		}

		$wikis = array();
		$stuff = $globalUser->queryAttached();
		foreach ( $stuff as $dbname => $value ) {
			$wikis[] = $dbname;
		}

		return array_combine( $wikis, $wikis );
	}
}
