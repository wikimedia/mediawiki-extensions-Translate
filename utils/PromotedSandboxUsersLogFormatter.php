<?php
/**
 * Formatter for promoted sandbox users log entries based on NewUsersLogFormatter.
 *
 * @file
 * @author Kartik Mistry
 * @license GPL-2.0+
 * @since 2014.01
 */

/**
 * This class formats new user log entries for users promoted from sandbox.
 *
 * @since 2014.01
 */
class PromotedSandboxUsersLogFormatter extends NewUsersLogFormatter {
	public function getComment() {
		return $this->msg( 'tsb-promoted-from-sandbox' )->escaped();
	}
}
