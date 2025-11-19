<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use Exception;

/**
 * Class to handle TtmServer specific exceptions.
 * @ingroup TTMServer
 */
class TtmServerException extends Exception {
	/** Exception code for transient errors when contacting the search backend */
	public const TRANSIENT_SEARCH_BACKEND_FAILURE = 10000;
}
