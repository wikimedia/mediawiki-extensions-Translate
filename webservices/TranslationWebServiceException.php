<?php
/**
 * Contains code related to web service support.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013 Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Used to signal a failure in an external web service. If the web service has
 * too many failures in a short period, it is suspended to avoid wasting time.
 * @since 2013-01-01
 * @ingroup TranslationWebService
 */
class TranslationWebServiceException extends MWException {
}
