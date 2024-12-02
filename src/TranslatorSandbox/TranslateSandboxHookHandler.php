<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use MediaWiki\Api\ApiLogout;
use MediaWiki\Api\ApiMessage;
use MediaWiki\Api\ApiOptions;
use MediaWiki\Api\Hook\ApiCheckCanExecuteHook;
use MediaWiki\Config\Config;
use MediaWiki\Permissions\Hook\TitleQuickPermissionsHook;
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
	UserGetRightsHook,
	TitleQuickPermissionsHook
{
	private bool $isTranslateSandboxEnabled;
	// right-translate-sandboxaction action-translate-sandboxaction
	private const ALLOWED_RIGHTS = [
		'editmyoptions',
		'editmyprivateinfo',
		'read',
		'readapi',
		'translate-sandboxaction',
		'viewmyprivateinfo',
	];

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

		$rights = self::ALLOWED_RIGHTS;

		// Do not let other hooks add more actions
		return false;
	}

	/** @inheritDoc */
	public function onTitleQuickPermissions( $title, $user, $action, &$errors, $doExpensiveQueries, $short ) {
		if ( !$this->isTranslateSandboxEnabled ) {
			return true;
		}

		if ( !TranslateSandbox::isSandboxed( $user ) ) {
			return true;
		}

		if ( !in_array( $action, self::ALLOWED_RIGHTS ) ) {
			// This is technically redundant (the userGetRights hook above will handle it)
			// but this displays a clearer error message.
			$errors = [ 'tsb-other-actions' ];
			return false;
		}

		return true;
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
			ApiOptions::class,
			// Allow logging out
			ApiLogout::class,
			// Allow marking the welcome notification as read
			\MediaWiki\Extension\Notifications\Api\ApiEchoMarkRead::class,
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
