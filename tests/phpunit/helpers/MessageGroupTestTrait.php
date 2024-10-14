<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\HookContainer\HookContainer;

/**
 * Trait to be used when performing MessageGroup related testing
 * @author Niklas Laxström
 * @since 2024.06
 * @license GPL-2.0-or-later
 */
trait MessageGroupTestTrait {
	public function setupGroupTestEnvironment( MediaWikiIntegrationTestCase $case ): void {
		$this->setupGroupTestEnvironmentWithConfig( $case, new MessageGroupTestConfig() );
	}

	public function setupGroupTestEnvironmentWithGroups( MediaWikiIntegrationTestCase $case, ?array $groups ): void {
		$config = new MessageGroupTestConfig();
		$config->groups = $groups;
		$this->setupGroupTestEnvironmentWithConfig( $case, $config );
	}

	public function setupGroupTestEnvironmentWithConfig(
		MediaWikiIntegrationTestCase $case,
		MessageGroupTestConfig $config
	): void {
		$case->overrideConfigValues( [
			'EnablePageTranslation' => true,
			'TranslateTranslationServices' => [],
			'TranslateDocumentationLanguageCode' => 'qqq',
			'TranslateMessageNamespaces' => [ NS_MEDIAWIKI ],
			'TranslateMessageIndex' => 'hash',
			'TranslateGroupFiles' => $config->translateGroupFiles
		] );

		if ( $config->groupInitLoaders ) {
			foreach ( $config->groupInitLoaders as $groupLoaders ) {
				$case->setTemporaryHook( 'TranslateInitGroupLoaders', $groupLoaders );
			}
		} else {
			$case->setTemporaryHook( 'TranslateInitGroupLoaders', HookContainer::NOOP );
		}

		$case->setTemporaryHook( 'TranslatePostInitGroups', HookContainer::NOOP );

		$mg = MessageGroups::singleton();
		$mg->clearProcessCache();
		if ( $config->groups ) {
			$mg->overrideGroupsForTesting( $config->groups );
		}

		if ( $config->skipMessageIndexRebuild ) {
			return;
		}

		Services::getInstance()->getMessageIndex()->rebuild();
	}
}
