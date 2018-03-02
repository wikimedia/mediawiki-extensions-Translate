<?php
/**
 * Contains code related to web service support.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Used to signal a configuration mistake in an external web service. This is in
 * contrast to TranslationWebServiceException that signals a failure in the web
 * service itself.
 * @since 2017.04
 * @ingroup TranslationWebService
 */
class TranslationWebServiceConfigurationException extends Exception {
}
