<?php


class TranslateSandboxReminderJob extends Job {
	public function newJob( array $params ) {
		return new self( Title::newMainPage(), $params );
	}

	function __construct( $title, $params, $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );
	}

	function run() {
		$status = UserMailer::send(
			$this->params['to'],
			$this->params['from'],
			$this->params['subj'],
			$this->params['body'],
			$this->params['replyto']
		);

		if ( $status->isOK() ) {
			$user = User::newFromId( 'user' );
			$reminders = $user->getOption( 'translate-sandbox-reminders' );
			if ( $reminders ) {
				$reminders = explode( '|', $reminders );
			} else {
				$reminders = array();
			}
			$reminders[] = wfTimestamp();
			$user->setOption( 'translate-sandbox-reminders', implode( '|', $reminders ) );
			$user->saveSettings();
		}

		return $status->isOk();
	}
}
