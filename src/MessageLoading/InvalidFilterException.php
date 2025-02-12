<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use Exception;

class InvalidFilterException extends Exception {
	public function __construct( string $type ) {
		parent::__construct( "Unknown filter $type" );
	}
}
