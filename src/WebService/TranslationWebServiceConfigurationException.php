<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\WebService;

use Exception;

/**
 * Used to signal a configuration mistake in an external web service. This is in
 * contrast to TranslationWebServiceException that signals a failure in the web
 * service itself.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2017.04
 * @ingroup TranslationWebService
 */
class TranslationWebServiceConfigurationException extends Exception {
}
