<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\PageTranslation;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiMain;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\Translate\MessageProcessing\MessageGroupMetadata;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Status\Status;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\IDBAccessObject;

/**
 * API module to mark a page for translation
 *
 * @author Tim Starling
 * @license GPL-2.0-or-later
 */
class MarkForTranslationActionApi extends ApiBase {
	private TranslatablePageMarker $translatablePageMarker;
	private MessageGroupMetadata $messageGroupMetadata;

	public function __construct(
		ApiMain $mainModule,
		string $moduleName,
		TranslatablePageMarker $translatablePageMarker,
		MessageGroupMetadata $messageGroupMetadata
	) {
		parent::__construct( $mainModule, $moduleName );
		$this->translatablePageMarker = $translatablePageMarker;
		$this->messageGroupMetadata = $messageGroupMetadata;
	}

	public function execute() {
		$this->checkUserRightsAny( 'pagetranslation' );

		$params = $this->extractRequestParams();
		$title = $this->getTitleFromTitleOrPageId( $params );
		$revision = $params['revid'] ?? null;

		$translateTitle = $this->getTriState( $params, 'translatetitle' );

		try {
			$operation = $this->translatablePageMarker->getMarkOperation(
				$title->toPageRecord( IDBAccessObject::READ_LATEST ),
				$revision,
				$translateTitle ?? true
			);
		} catch ( TranslatablePageMarkException $e ) {
			$this->addError( $e->getMessageObject() );
			return;
		}

		$unitNameValidationResult = $operation->getUnitValidationStatus();
		if ( !$unitNameValidationResult->isOK() ) {
			$this->addMessagesFromStatus( $unitNameValidationResult );
			return;
		}

		if ( $translateTitle === null ) {
			// Check whether page title was previously marked for translation.
			// If the page is marked for translation the first time, default to
			// allowing title translation, unless the page is a template. T305240
			$translateTitle = (
					$operation->isFirstMark() &&
					!$title->inNamespace( NS_TEMPLATE )
				) || $operation->getPage()->hasPageDisplayTitle();
		}

		// By default, units are marked nofuzzy if only their tvars have changed
		$noFuzzyUnits = [];
		foreach ( $operation->getUnits() as $s ) {
			if ( $s->type === 'changed' && $s->onlyTvarsChanged() ) {
				$noFuzzyUnits[] = $s->id;
			}
		}

		// Add and subtract nofuzzy flags as specified by the user
		$noFuzzyUnits = array_unique( array_merge( $noFuzzyUnits, $params['nofuzzyunits'] ?? [] ) );
		$noFuzzyUnits = array_diff( $noFuzzyUnits, $params['fuzzyunits'] ?? [] );

		$groupId = $operation->getPage()->getMessageGroupId();
		if ( isset( $params['prioritylanguages'] ) ) {
			// Set priority languages
			$priorityLanguages = $params['prioritylanguages'];
			$priorityLanguageStatus = $this->validatePriorityLanguages( $priorityLanguages );
			if ( !$priorityLanguageStatus->isOK() ) {
				$this->addMessagesFromStatus( $priorityLanguageStatus );
				return;
			}
			$forcePriority = $params['forcepriority'] ?? false;
			$priorityReason = $params['priorityreason'] ?? '';
		} else {
			// markForTranslation() sets priority languages unconditionally.
			// If no changes were requested, we need to load the current values
			// just to avoid changing it.
			$blob = (string)$this->messageGroupMetadata->get( $groupId, 'prioritylangs' );
			$priorityLanguages = $blob !== '' ? explode( ',', $blob ) : [];
			$forcePriority = $this->messageGroupMetadata->get( $groupId, 'priorityforce' ) === 'on';
			// If no priority reason is set, set it to an empty string
			$priorityReason = $this->messageGroupMetadata->get( $groupId, 'priorityreason' );
			$priorityReason = $priorityReason !== false ? $priorityReason : '';
		}

		$transclusion = $this->getTriState( $params, 'transclusion' );
		if ( $transclusion === null ) {
			$transclusion = $operation->getPage()->supportsTransclusion() ?? $operation->isFirstMark();
		}

		$translatablePageSettings = new TranslatablePageSettings(
			$priorityLanguages,
			$forcePriority,
			$priorityReason,
			$noFuzzyUnits,
			$translateTitle,
			$params[ 'forcelatestsyntaxversion'] ?? false,
			$transclusion
		);

		try {
			$unitCount = $this->translatablePageMarker->markForTranslation(
				$operation,
				$translatablePageSettings,
				RequestContext::getMain(),
				$this->getUser()
			);
		} catch ( TranslatablePageMarkException $e ) {
			$this->addError( $e->getMessageObject() );
			return;
		}
		$res = [
			'result' => 'Success',
			'firstmark' => $operation->isFirstMark(),
			'unitcount' => $unitCount,
		];
		$this->getResult()->addValue( null, $this->getModuleName(), $res );
	}

	/** Get a nullable boolean parameter */
	private function getTriState( array $params, string $name ): ?bool {
		return isset( $params[$name] ) ? $params[$name] === 'yes' : null;
	}

	private function validatePriorityLanguages( array $priorityLanguageCodes ): Status {
		$knownLanguageCodes = array_keys( Utilities::getLanguageNames( 'en' ) );
		$invalidLanguageCodes = array_diff( $priorityLanguageCodes, $knownLanguageCodes );
		$context = $this->getContext();

		if ( $invalidLanguageCodes ) {
			return Status::newFatal(
				$context->msg( 'apierror-markfortranslation-invalid-prioritylangs' )
					->params(
						count( $invalidLanguageCodes ),
						$context->getLanguage()->commaList( $invalidLanguageCodes )
					)
			);
		}

		return Status::newGood();
	}

	/** @inheritDoc */
	public function isWriteMode() {
		return true;
	}

	/** @inheritDoc */
	public function needsToken() {
		return 'csrf';
	}

	protected function getAllowedParams(): array {
		return [
			'title' => [
				ParamValidator::PARAM_TYPE => 'string',
			],
			'pageid' => [
				ParamValidator::PARAM_TYPE => 'integer',
			],
			'revid' => [
				ParamValidator::PARAM_TYPE => 'integer',
			],
			'translatetitle' => [
				ParamValidator::PARAM_TYPE => [ 'yes', 'no' ],
			],
			'prioritylanguages' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_ISMULTI => true,
				ParamValidator::PARAM_ISMULTI_LIMIT1 => 1000,
				ParamValidator::PARAM_ISMULTI_LIMIT2 => 1000,
			],
			'forcepriority' => [
				ParamValidator::PARAM_TYPE => 'boolean',
			],
			'priorityreason' => [
				ParamValidator::PARAM_TYPE => 'string',
			],
			'nofuzzyunits' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_ISMULTI => true,
				ParamValidator::PARAM_ISMULTI_LIMIT1 => 1000,
				ParamValidator::PARAM_ISMULTI_LIMIT2 => 1000,
			],
			'fuzzyunits' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_ISMULTI => true,
				ParamValidator::PARAM_ISMULTI_LIMIT1 => 1000,
				ParamValidator::PARAM_ISMULTI_LIMIT2 => 1000,
			],
			'forcelatestsyntaxversion' => [
				ParamValidator::PARAM_TYPE => 'boolean',
			],
			'transclusion' => [
				ParamValidator::PARAM_TYPE => [ 'yes', 'no' ],
			],
		];
	}

}
