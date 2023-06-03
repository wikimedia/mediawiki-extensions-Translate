<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use Exception;

/**
 * Used to signal a failure in an external web service. If the web service has
 * too many failures in a short period, it is suspended to avoid wasting time.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013-01-01
 * @ingroup TranslationWebService
 */
class TranslationWebServiceException extends Exception {
}
