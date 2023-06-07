<?php
declare( strict_types = 1 );

// phpcs:disable MediaWiki.NamingConventions.LowerCamelFunctionsName.FunctionName

namespace MediaWiki\Extension\Translate;

use MediaWiki\Extension\Translate\MessageGroupProcessing\EventMessageGroupStateChangeHook;
use MediaWiki\Extension\Translate\MessageGroupProcessing\EventMessageMembershipChangeHook;
use MediaWiki\Extension\Translate\MessageGroupProcessing\GetAPIMessageGroupsParameterListHook;
use MediaWiki\Extension\Translate\MessageGroupProcessing\GetAPIMessageGroupsPropertyDescsHook;
use MediaWiki\Extension\Translate\MessageGroupProcessing\InitGroupLoadersHook;
use MediaWiki\Extension\Translate\MessageGroupProcessing\ModifyMessageGroupStatesHook;
use MediaWiki\Extension\Translate\MessageGroupProcessing\PostInitGroupsHook;
use MediaWiki\Extension\Translate\MessageGroupProcessing\ProcessAPIMessageGroupsPropertiesHook;
use MediaWiki\Extension\Translate\TranslatorInterface\Aid\PrefillTranslationHook;
use MediaWiki\Extension\Translate\TranslatorInterface\BeforeAddModulesHook;
use MediaWiki\Extension\Translate\TranslatorInterface\EventTranslationReviewHook;
use MediaWiki\Extension\Translate\TranslatorInterface\GetSpecialTranslateOptionsHook;
use MediaWiki\Extension\Translate\TranslatorInterface\NewTranslationHook;
use MediaWiki\Extension\Translate\TranslatorSandbox\UserPromotedHook;
use MediaWiki\Extension\Translate\Utilities\SupportedLanguagesHook;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\User\UserIdentity;
use MessageGroup;
use MessageHandle;
use User;

/**
 * Hook runner for the Translate extension.
 *
 * This is a hook runner class, see docs/Hooks.md in core.
 * @internal
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
	NewTranslationHook,
	ModifyMessageGroupStatesHook,
	EventMessageGroupStateChangeHook,
	GetAPIMessageGroupsParameterListHook,
	GetAPIMessageGroupsPropertyDescsHook,
	InitGroupLoadersHook,
	PostInitGroupsHook,
	ProcessAPIMessageGroupsPropertiesHook,
	SupportedLanguagesHook,
	EventMessageMembershipChangeHook
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

	public function onTranslate_modifyMessageGroupStates( string $groupId, array &$conf ) {
		return $this->hookContainer->run( 'Translate:modifyMessageGroupStates', [ $groupId, &$conf ] );
	}

	public function onTranslateEventMessageGroupStateChange(
		MessageGroup $group,
		string $code,
		$oldState,
		string $newState
	) {
		return $this->hookContainer->run( 'TranslateEventMessageGroupStateChange',
			[ $group, $code, $oldState, $newState ] );
	}

	public function onTranslateGetAPIMessageGroupsParameterList( array &$params ) {
		return $this->hookContainer->run( 'TranslateGetAPIMessageGroupsParameterList', [ &$params ] );
	}

	public function onTranslateGetAPIMessageGroupsPropertyDescs( array &$properties ) {
		return $this->hookContainer->run( 'TranslateGetAPIMessageGroupsPropertyDescs', [ &$properties ] );
	}

	public function onTranslateInitGroupLoaders( array &$groupLoader, array $deps ) {
		return $this->hookContainer->run( 'TranslateInitGroupLoaders', [ &$groupLoader, $deps ] );
	}

	public function onTranslatePostInitGroups( array &$groups, array &$deps, array &$autoload ) {
		return $this->hookContainer->run( 'TranslatePostInitGroups', [ &$groups, &$deps, &$autoload ] );
	}

	public function onTranslateProcessAPIMessageGroupsProperties(
		array &$a,
		array $props,
		array $params,
		MessageGroup $g
	) {
		return $this->hookContainer->run( 'TranslateProcessAPIMessageGroupsProperties', [ &$a, $props, $params, $g ] );
	}

	public function onTranslateSupportedLanguages( array &$list, ?string $language ) {
		return $this->hookContainer->run( 'TranslateSupportedLanguages', [ &$list, $language ] );
	}

	public function onTranslateEventMessageMembershipChange( MessageHandle $handle, array $old, array $new ) {
		return $this->hookContainer->run( 'TranslateEventMessageMembershipChange', [ $handle, $old, $new ] );
	}
}
