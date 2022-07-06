<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use ApiBase;
use ApiMain;
use ManualLogEntry;
use Mediawiki\Languages\LanguageNameUtils;
use MediaWiki\MediaWikiServices;
use MessageGroup;
use MessageGroups;
use SpecialPage;
use User;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module for switching workflow states for message groups
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class GroupReviewActionApi extends ApiBase {
	protected static $right = 'translate-groupreview';
	/** @var LanguageNameUtils */
	private $languageNameUtils;

	public function __construct( ApiMain $main, string $action, LanguageNameUtils $languageNameUtils ) {
		parent::__construct( $main, $action );
		$this->languageNameUtils = $languageNameUtils;
	}

	public function execute() {
		$user = $this->getUser();
		$requestParams = $this->extractRequestParams();

		$group = MessageGroups::getGroup( $requestParams['group'] );
		$code = $requestParams['language'];

		if ( !$group || MessageGroups::isDynamic( $group ) ) {
			$this->dieWithError( [ 'apierror-badparameter', 'group' ] );
		}
		$stateConfig = $group->getMessageGroupStates()->getStates();
		if ( !$stateConfig ) {
			$this->dieWithError( 'apierror-translate-groupreviewdisabled', 'disabled' );
		}

		$this->checkUserRightsAny( self::$right );

		if ( $user->getBlock() ) {
			$this->dieBlocked( $user->getBlock() );
		}

		$languages = $this->languageNameUtils->getLanguageNames();
		if ( !isset( $languages[$code] ) ) {
			$this->dieWithError( [ 'apierror-badparameter', 'language' ] );
		}

		$targetState = $requestParams['state'];
		if ( !isset( $stateConfig[$targetState] ) ) {
			$this->dieWithError( 'apierror-translate-invalidstate', 'invalidstate' );
		}

		if ( is_array( $stateConfig[$targetState] )
			&& isset( $stateConfig[$targetState]['right'] )
		) {
			$this->checkUserRightsAny( $stateConfig[$targetState]['right'] );
		}

		self::changeState( $group, $code, $targetState, $user );

		$output = [ 'review' => [
			'group' => $group->getId(),
			'language' => $code,
			'state' => $targetState,
		] ];

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	/** @return mixed|false — The value from the field, or false if nothing was found */
	public static function getState( MessageGroup $group, string $code ) {
		$dbw = MediaWikiServices::getInstance()->getDBLoadBalancer()->getMaintenanceConnectionRef( DB_PRIMARY );
		$table = 'translate_groupreviews';

		$field = 'tgr_state';
		$conds = [
			'tgr_group' => $group->getId(),
			'tgr_lang' => $code
		];

		return $dbw->selectField( $table, $field, $conds, __METHOD__ );
	}

	public static function changeState( MessageGroup $group, string $code, string $newState, User $user ): bool {
		$currentState = self::getState( $group, $code );
		if ( $currentState === $newState ) {
			return false;
		}

		$table = 'translate_groupreviews';
		$index = [ 'tgr_group', 'tgr_lang' ];
		$row = [
			'tgr_group' => $group->getId(),
			'tgr_lang' => $code,
			'tgr_state' => $newState,
		];
		$mwServices = MediaWikiServices::getInstance();
		$dbw = $mwServices->getDBLoadBalancer()->getMaintenanceConnectionRef( DB_PRIMARY );
		$dbw->replace( $table, [ $index ], $row, __METHOD__ );

		$entry = new ManualLogEntry( 'translationreview', 'group' );
		$entry->setPerformer( $user );
		$entry->setTarget( SpecialPage::getTitleFor( 'Translate', $group->getId() ) );
		// @todo
		// $entry->setComment( $comment );
		$entry->setParameters( [
			'4::language' => $code,
			'5::group-label' => $group->getLabel(),
			'6::old-state' => $currentState,
			'7::new-state' => $newState,
		] );

		$logid = $entry->insert();
		$entry->publish( $logid );

		$mwServices->getHookContainer()->run( 'TranslateEventMessageGroupStateChange',
			[ $group, $code, $currentState, $newState ] );

		return true;
	}

	public function isWriteMode(): bool {
		return true;
	}

	public function needsToken(): string {
		return 'csrf';
	}

	protected function getAllowedParams(): array {
		return [
			'group' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'language' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => 'en',
			],
			'state' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'token' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
		];
	}

	protected function getExamplesMessages(): array {
		return [
			'action=groupreview&group=page-Example&language=de&state=ready&token=foo'
				=> 'apihelp-groupreview-example-1',
		];
	}
}
