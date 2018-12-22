<?php

class TranslateSandboxEmailJob extends Job {
	/**
	 * @param array $params
	 * @return self
	 */
	public static function newJob( array $params ) {
		return new self( Title::newMainPage(), $params );
	}

	/**
	 * @param Title $title
	 * @param array $params
	 */
	public function __construct( $title, $params ) {
		parent::__construct( __CLASS__, $title, $params );
	}

	public function run() {
		$status = UserMailer::send(
			$this->params['to'],
			$this->params['from'],
			$this->params['subj'],
			$this->params['body'],
			[ 'replyTo' => $this->params['replyto'] ]
		);

		$isOK = $status->isOK();

		if ( $isOK && $this->params['emailType'] === 'reminder' ) {
			$user = User::newFromId( $this->params['user'] );

			$reminders = $user->getOption( 'translate-sandbox-reminders' );
			$reminders = $reminders ? explode( '|', $reminders ) : [];
			$reminders[] = wfTimestamp();
			$user->setOption( 'translate-sandbox-reminders', implode( '|', $reminders ) );

			$reminders = $user->getOption( 'translate-sandbox-reminders' );
			$user->setOption( 'translate-sandbox-reminders', $reminders );
			$user->saveSettings();
		}

		return $isOK;
	}
}
