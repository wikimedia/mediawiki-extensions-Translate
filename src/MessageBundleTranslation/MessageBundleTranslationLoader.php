<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageBundleTranslation;

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use RuntimeException;

class MessageBundleTranslationLoader {
	public function get( MessageBundle $messageBundle, string $languageCode ): array {
		$messageBundleGroup = MessageGroups::getGroup( $messageBundle->getMessageGroupId() );
		$messageBundleTitle = $messageBundle->getTitle();
		if ( !$messageBundleGroup ) {
			throw new RuntimeException(
				"Did not find message group for message bundle: {$messageBundleTitle->getPrefixedText()}"
			);
		}
		$collection = $messageBundleGroup->initCollection( $languageCode );
		$collection->loadTranslations();

		$translations = [];
		foreach ( $collection as $key => $message ) {
			$translations[ str_replace( "{$messageBundleTitle->getDBkey()}/", '', $key ) ] =
				$message->translation();
		}

		return $translations;
	}
}
