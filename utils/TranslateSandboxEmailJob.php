<?php

use MediaWiki\MediaWikiServices;

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
		$services = MediaWikiServices::getInstance();
		$status = $services
			->getEmailer()
			->send(
				[ $this->params['to'] ],
				$this->params['from'],
				$this->params['subj'],
				$this->params['body'],
				null,
				[ 'replyTo' => $this->params['replyto'] ]
			);

		return $status->isOK();
	}
}
