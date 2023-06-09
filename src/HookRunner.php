<?php
declare( strict_types = 1 );

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\Extension\Translate;

use MediaWiki\Extension\Translate\TranslatorInterface\Aid\PrefillTranslationHook;
use MediaWiki\Extension\Translate\TranslatorInterface\BeforeAddModulesHook;
use MediaWiki\Extension\Translate\TranslatorInterface\EventTranslationReviewHook;
use MediaWiki\Extension\Translate\TranslatorInterface\GetSpecialTranslateOptionsHook;
use MediaWiki\Extension\Translate\TranslatorInterface\NewTranslationHook;
use MediaWiki\Extension\Translate\TranslatorSandbox\UserPromotedHook;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\User\UserIdentity;
use MessageHandle;
use User;

/**
 * Hook runner for the Translate extension.
 *
 * Some legacy style hooks have not been converted to interfaces yet.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2023.03
 */
class HookRunner implements
	UserPromotedHook,
	PrefillTranslationHook,
	BeforeAddModulesHook,
	EventTranslationReviewHook,
	GetSpecialTranslateOptionsHook,
	NewTranslationHook
{
	private HookContainer $hookContainer;

	public function __construct( HookContainer $hookContainer ) {
		$this->hookContainer = $hookContainer;
	}

	public function onTranslate_TranslatorSandbox_UserPromoted( UserIdentity $user ): void {
		$this->hookContainer->run( 'Translate:TranslatorSandbox:UserPromoted', [ $user ], [ 'abortable' => false ] );
	}

	public function onTranslatePrefillTranslation( ?string &$translation, MessageHandle $handle ) {
		return $this->hookContainer->run( 'TranslatePrefillTranslation', [ &$translation, $handle ] );
	}

	public function onTranslateBeforeAddModules( array &$modules ) {
		return $this->hookContainer->run( 'TranslateBeforeAddModules', [ &$modules ] );
	}

	public function onTranslateEventTranslationReview( MessageHandle $handle ) {
		return $this->hookContainer->run( 'TranslateEventTranslationReview', [ $handle ] );
	}

	public function onTranslateGetSpecialTranslateOptions( array &$defaults, array &$nonDefaults ) {
		return $this->hookContainer->run( 'TranslateGetSpecialTranslateOptions', [ &$defaults, &$nonDefaults ] );
	}

	public function onTranslate_newTranslation( MessageHandle $handle, int $revisionId, string $text, User $user ) {
		return $this->hookContainer->run( 'Translate:newTranslation', [ $handle, $revisionId, $text, $user ] );
	}
}
