<?php

class TranslateSandboxEmailJob extends Job {
	/**
	 * @param array $params
	 * @return TranslateSandboxEmailJob
	 */
	public static function newJob( array $params ) {
		return new self( Title::newMainPage(), $params );
	}

	/**
	 * @param Title $title
	 * @param array $params
	 * @param int $id
	 */
	public function __construct( $title, $params, $id = 0 ) {
		parent::__construct( __CLASS__, $title, $params, $id );
	}

	public function run() {
		global $wgVersion;
		$status = UserMailer::send(
			$this->params['to'],
			$this->params['from'],
			$this->params['subj'],
			$this->params['body'],
			version_compare( $wgVersion, '1.26.0', '<' )
				? $this->params['replyto']
				: array( 'replyTo' => $this->params['replyto'] )
		);

		$isOK = $status->isOK();

		if ( $isOK && $this->params['emailType'] === 'reminder' ) {
			$user = User::newFromId( $this->params['user'] );

			$reminders = $user->getOption( 'translate-sandbox-reminders' );
			$reminders = $reminders ? explode( '|', $reminders ) : array();
			$reminders[] = wfTimestamp();
			$user->setOption( 'translate-sandbox-reminders', implode( '|', $reminders ) );

			$reminders = $user->getOption( 'translate-sandbox-reminders' );
			$user->setOption( 'translate-sandbox-reminders', $reminders );
			$user->saveSettings();
		}

		return $isOK;
	}
}
