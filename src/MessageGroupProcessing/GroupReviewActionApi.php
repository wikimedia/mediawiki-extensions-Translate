<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use ApiBase;
use ApiMain;
use Mediawiki\Languages\LanguageNameUtils;
use MessageGroups;
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
	/** @var MessageGroupReview */
	private $messageGroupReview;

	public function __construct(
		ApiMain $main,
		string $action,
		LanguageNameUtils $languageNameUtils,
		MessageGroupReview $messageGroupReview
	) {
		parent::__construct( $main, $action );
		$this->languageNameUtils = $languageNameUtils;
		$this->messageGroupReview = $messageGroupReview;
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

		$this->messageGroupReview->changeState( $group, $code, $targetState, $user );

		$output = [ 'review' => [
			'group' => $group->getId(),
			'language' => $code,
			'state' => $targetState,
		] ];

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
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
