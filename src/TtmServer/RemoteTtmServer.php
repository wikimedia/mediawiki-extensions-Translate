<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

/**
 * Class for handling remote TTMServers over MediaWiki API.
 * Currently, querying is done in TranslationHelpers, and
 * this class only handles location retrieval.
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup TTMServer
 */
class RemoteTtmServer extends TtmServer implements ReadableTtmServer {
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
