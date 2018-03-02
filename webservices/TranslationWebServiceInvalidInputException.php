<?php
/**
 * Contains code related to web service support.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Used to signal that the requested input is rejected and cannot be used with
 * an external web service. This is in contrast to a failure in the web service
 * itself that is not in our control. Most common case for this is input that is
 * too long.
 * service itself.
 * @since 2017.04
 * @ingroup TranslationWebService
 */
class TranslationWebServiceInvalidInputException extends Exception {
}
