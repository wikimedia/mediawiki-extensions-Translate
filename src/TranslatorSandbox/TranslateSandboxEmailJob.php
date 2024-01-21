<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use Job;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class TranslateSandboxEmailJob extends Job {

	public static function newJob( array $params ): self {
		return new self( Title::newMainPage(), $params );
	}

	public function __construct( Title $title, array $params ) {
		parent::__construct( 'TranslateSandboxEmailJob', $title, $params );
	}

	public function run(): bool {
		$status = MediaWikiServices::getInstance()
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
