<?php
/**
 * TTMServer - The Translate extension translation memory interface
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup TTMServer
 */

use MediaWiki\Extension\Translate\TtmServer\ReadableTtmServer;

/**
 * Class for handling remote TTMServers over MediaWiki API.
 * Currently querying is done in TranslationHelpers, and
 * this class only handles location retrieval.
 * @since 2012-06-27
 * @ingroup TTMServer
 */
class RemoteTTMServer extends TTMServer implements ReadableTtmServer {
	public function query( string $sourceLanguage, string $targetLanguage, string $text ): array {
		// @todo Implement some day perhaps?
		return [];
	}

	public function isLocalSuggestion( array $suggestion ): bool {
		return false;
	}

	public function expandLocation( array $suggestion ): string {
		return $suggestion['location'];
	}
}
