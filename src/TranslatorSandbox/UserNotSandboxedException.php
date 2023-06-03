<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use Exception;

class UserNotSandboxedException extends Exception {
	public function __construct() {
		parent::__construct( 'User is not sandboxed' );
	}
}
