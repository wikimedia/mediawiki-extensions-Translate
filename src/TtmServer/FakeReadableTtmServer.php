<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

/**
 * NO-OP readable version of TtmServer when it is disabled.
 * @ingroup TTMServer
 */
class FakeReadableTtmServer extends TtmServer implements ReadableTtmServer {
	public function query( string $sourceLanguage, string $targetLanguage, string $text ): array {
		return [];
	}

	public function isLocalSuggestion( array $suggestion ): bool {
		return false;
	}

	public function expandLocation( array $suggestion ): string {
		return '';
	}
}
