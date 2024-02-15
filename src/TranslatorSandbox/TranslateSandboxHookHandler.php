<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use ApiMessage;
use Config;
use MediaWiki\Api\Hook\ApiCheckCanExecuteHook;
use MediaWiki\Permissions\Hook\UserGetRightsHook;
use MediaWiki\Preferences\Hook\GetPreferencesHook;

/**
 * Hook handler for the TranslateSandbox.
 * @author Eugene Wang'ombe
 * @license GPL-2.0-or-later
 * @since 2023.12
 */
class TranslateSandboxHookHandler implements
	GetPreferencesHook,
	ApiCheckCanExecuteHook,
	UserGetRightsHook
{
	private bool $isTranslateSandboxEnabled;

	public function __construct( Config $config ) {
		$this->isTranslateSandboxEnabled = $config->get( 'TranslateUseSandbox' );
	}

	/** @inheritDoc */
	public function onUserGetRights( $user, &$rights ): bool {
		if ( !$this->isTranslateSandboxEnabled ) {
			return true;
		}

		if ( !TranslateSandbox::isSandboxed( $user ) ) {
			return true;
		}
		// right-translate-sandboxaction action-translate-sandboxaction
		$rights = [
			'editmyoptions',
			'editmyprivateinfo',
			'read',
			'readapi',
			'translate-sandboxaction',
			'viewmyprivateinfo',
		];

		// Do not let other hooks add more actions
		return false;
	}

	/** @inheritDoc */
	public function onGetPreferences( $user, &$preferences ): void {
		if ( !$this->isTranslateSandboxEnabled ) {
			return;
		}

		$preferences['translate-sandbox'] = $preferences['translate-sandbox-reminders'] =
			[ 'type' => 'api' ];
	}

	/**
	 * Inclusion listing for certain API modules. See also onUserGetRights.
	 * @inheritDoc
	 */
	public function onApiCheckCanExecute( $module, $user, &$message ): bool {
		if ( !$this->isTranslateSandboxEnabled ) {
			return true;
		}

		$inclusionList = [
			// Obviously this is needed to get out of the sandbox
			TranslationStashActionApi::class,
			// Used by UniversalLanguageSelector for example
			'ApiOptions'
		];

		if ( TranslateSandbox::isSandboxed( $user ) ) {
			$class = get_class( $module );
			if ( $module->isWriteMode() && !in_array( $class, $inclusionList, true ) ) {
				$message = ApiMessage::create( 'apierror-writeapidenied' );
				return false;
			}
		}

		return true;
	}
}
