<?php
/**
 * Translation aid provider.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Translation aid which gives suggestion from translation memory.
 *
 * @ingroup TranslationAids
 * @since 2013-01-01
 */
class TTMServerAid extends TranslationAid {
	public function getData() {
		$suggestions = array();

		$text = $this->getDefinition();
		$from = $this->group->getSourceLanguage();
		$to = $this->handle->getCode();

		global $wgTranslateTranslationServices;
		foreach ( $wgTranslateTranslationServices as $name => $config ) {
			$server = TTMServer::factory( $config );

			try {
				if ( $server instanceof RemoteTTMServer ) {
					$service = TranslationWebService::factory( $name, $config );
					$query = $service->getSuggestions( array( $from => $text ), $from, $to );
				} elseif ( $server instanceof ReadableTTMServer ) {
					$query = $server->query( $from, $to, $text );
				} else {
					continue;
				}
			} catch ( Exception $e ) {
				// Not ideal to catch all exceptions
				continue;
			}

			foreach ( $query as $item ) {
				$item['service'] = $name;
				$item['source_language'] = $from;
				$item['local'] = $server->isLocalSuggestion( $item );
				$item['uri'] = $server->expandLocation( $item );
				$suggestions[] = $item;
			}
		}

		$suggestions = TTMServer::sortSuggestions( $suggestions );
		$suggestions['**'] = 'suggestion';

		return $suggestions;
	}
}
