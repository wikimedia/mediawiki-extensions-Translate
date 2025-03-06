<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageLoading;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiPageSet;
use MediaWiki\Api\ApiQuery;
use MediaWiki\Api\ApiQueryGeneratorBase;
use MediaWiki\Api\ApiResult;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroupReviewStore;
use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Utilities\ConfigHelper;
use MediaWiki\Extension\Translate\Utilities\Utilities;
use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Title\Title;
use RecentMessageGroup;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\ParamValidator\TypeDef\IntegerDef;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * Api module for querying MessageCollection.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup API TranslateAPI
 */
class QueryMessageCollectionActionApi extends ApiQueryGeneratorBase {
	private ConfigHelper $configHelper;
	private LanguageNameUtils $languageNameUtils;
	private ILoadBalancer $loadBalancer;
	private MessageGroupReviewStore $groupReviewStore;

	public function __construct(
		ApiQuery $query,
		string $moduleName,
		ConfigHelper $configHelper,
		LanguageNameUtils $languageNameUtils,
		ILoadBalancer $loadBalancer,
		MessageGroupReviewStore $groupReviewStore
	) {
		parent::__construct( $query, $moduleName, 'mc' );
		$this->configHelper = $configHelper;
		$this->languageNameUtils = $languageNameUtils;
		$this->loadBalancer = $loadBalancer;
		$this->groupReviewStore = $groupReviewStore;
	}

	public function execute(): void {
		$this->run();
	}

	/** @inheritDoc */
	public function getCacheMode( $params ): string {
		return 'public';
	}

	/** @inheritDoc */
	public function executeGenerator( $resultPageSet ): void {
		$this->run( $resultPageSet );
	}

	private function validateLanguageCode( string $code ): void {
		if ( !Utilities::isSupportedLanguageCode( $code ) ) {
			$this->dieWithError( [ 'apierror-translate-invalidlanguage', $code ] );
		}
	}

	private function run( ?ApiPageSet $resultPageSet = null ): void {
		$params = $this->extractRequestParams();

		$group = MessageGroups::getGroup( $params['group'] );
		if ( !$group ) {
			$this->dieWithError( [ 'apierror-badparameter', 'mcgroup' ] );
		}

		$languageCode = $params[ 'language' ];
		$this->validateLanguageCode( $languageCode );
		$sourceLanguageCode = $group->getSourceLanguage();

		// Even though translation to source language maybe disabled, we still want to
		// fetch the message collections for the source language.
		if ( $sourceLanguageCode === $languageCode ) {
			$name = $this->getLanguageName( $languageCode );
			$this->addWarning( [ 'apiwarn-translate-language-disabled-source', wfEscapeWikiText( $name ) ] );
		}

		$isDisabled = $this->configHelper->isTargetLanguageDisabled( $group, $languageCode, $reason );
		if ( $isDisabled ) {
			$name = $this->getLanguageName( $languageCode );
			if ( $reason === null ) {
				$this->dieWithError( [ 'apierror-translate-language-disabled', $name ] );
			} else {
				$this->dieWithError( [ 'apierror-translate-language-disabled-reason', $name, $reason ] );
			}
		}

		// A check for cases where the source language of group messages
		// is a variant of the target language being translated into.
		if ( strtok( $sourceLanguageCode, '-' ) === strtok( $languageCode, '-' ) ) {
			$sourceLanguageName = $this->getLanguageName( $sourceLanguageCode );
			$targetLanguageName = $this->getLanguageName( $languageCode );
			$this->addWarning( [
				'apiwarn-translate-language-targetlang-variant-of-source',
				wfEscapeWikiText( $targetLanguageName ),
				wfEscapeWikiText( $sourceLanguageName ) ]
			);
		}

		if ( MessageGroups::isDynamic( $group ) ) {
			/** @var RecentMessageGroup $group */
			// @phan-suppress-next-line PhanUndeclaredMethod
			$group->setLanguage( $params['language'] );
		}

		$messages = $group->initCollection( $params['language'] );

		foreach ( $params['filter'] as $filter ) {
			if ( $filter === '' || $filter === null ) {
				continue;
			}

			$value = null;
			if ( str_contains( $filter, ':' ) ) {
				[ $filter, $value ] = explode( ':', $filter, 2 );
			}
			/* The filtering params here are swapped wrt MessageCollection.
			 * There (fuzzy) means do not show fuzzy, which is the same as !fuzzy
			 * here and fuzzy here means (fuzzy, false) there. */
			try {
				$value = $value === null ? $value : (int)$value;
				if ( str_starts_with( $filter, '!' ) ) {
					$messages->filter( substr( $filter, 1 ), MessageCollection::EXCLUDE_MATCHING, $value );
				} else {
					$messages->filter( $filter, MessageCollection::INCLUDE_MATCHING, $value );
				}
			} catch ( InvalidFilterException $e ) {
				$this->dieWithError(
					[ 'apierror-translate-invalidfilter', wfEscapeWikiText( $e->getMessage() ) ],
					'invalidfilter'
				);
			}
		}

		$resultSize = count( $messages );
		$offsets = $messages->slice( $params['offset'], $params['limit'] );
		$batchSize = count( $messages );
		[ /*$backwardsOffset*/, $forwardsOffset, $startOffset ] = $offsets;

		$result = $this->getResult();
		$result->addValue(
			[ 'query', 'metadata' ],
			'state',
			$this->groupReviewStore->getWorkflowState( $group->getId(), $params['language'] )
		);

		$result->addValue( [ 'query', 'metadata' ], 'resultsize', $resultSize );
		$result->addValue(
			[ 'query', 'metadata' ],
			'remaining',
			$resultSize - $startOffset - $batchSize
		);

		$messages->loadTranslations();

		$pages = [];

		if ( $forwardsOffset !== false ) {
			$this->setContinueEnumParameter( 'offset', $forwardsOffset );
		}

		$props = array_flip( $params['prop'] );

		/** @var Title $title */
		foreach ( $messages->keys() as $mkey => $titleValue ) {
			$title = Title::newFromLinkTarget( $titleValue );

			if ( $resultPageSet === null ) {
				$data = $this->extractMessageData( $result, $props, $messages[$mkey] );
				$data['title'] = $title->getPrefixedText();
				$data['targetLanguage'] = $messages->getLanguage();

				$handle = new MessageHandle( $title );

				if ( $handle->isValid() ) {
					$data['primaryGroup'] = $handle->getGroup()->getId();
				}

				$result->addValue( [ 'query', $this->getModuleName() ], null, $data );
			} else {
				$pages[] = $title;
			}
		}

		if ( $resultPageSet === null ) {
			$result->addIndexedTagName(
				[ 'query', $this->getModuleName() ],
				'message'
			);
		} else {
			$resultPageSet->populateFromTitles( $pages );
		}
	}

	private function getLanguageName( string $languageCode ): string {
		return $this
			->languageNameUtils
			->getLanguageName( $languageCode, $this->getLanguage()->getCode() );
	}

	private function extractMessageData(
		ApiResult $result,
		array $props,
		Message $message
	): array {
		$data = [ 'key' => $message->key() ];

		if ( isset( $props['definition'] ) ) {
			$data['definition'] = $message->definition();
		}
		if ( isset( $props['translation'] ) ) {
			// Remove !!FUZZY!! from translation if present.
			$translation = $message->translation();
			if ( $translation !== null ) {
				$translation = str_replace( TRANSLATE_FUZZY, '', $translation );
			}
			$data['translation'] = $translation;
		}
		if ( isset( $props['tags'] ) ) {
			$data['tags'] = $message->getTags();
			$result->setIndexedTagName( $data['tags'], 'tag' );
		}
		if ( isset( $props['properties'] ) ) {
			foreach ( $message->getPropertyNames() as $prop ) {
				$data['properties'][$prop] = $message->getProperty( $prop );
				ApiResult::setIndexedTagNameRecursive( $data['properties'], 'val' );
			}
		}

		return $data;
	}

	/** @inheritDoc */
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
			'limit' => [
				ParamValidator::PARAM_DEFAULT => 500,
				ParamValidator::PARAM_TYPE => 'limit',
				IntegerDef::PARAM_MIN => 1,
				IntegerDef::PARAM_MAX => ApiBase::LIMIT_BIG2,
				IntegerDef::PARAM_MAX2 => ApiBase::LIMIT_BIG2,
			],
			'offset' => [
				ParamValidator::PARAM_DEFAULT => '',
				ParamValidator::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			],
			'filter' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_DEFAULT => '!optional|!ignored',
				ParamValidator::PARAM_ISMULTI => true,
			],
			'prop' => [
				ParamValidator::PARAM_TYPE => [
					'definition',
					'translation',
					'tags',
					'properties',
				],
				ParamValidator::PARAM_DEFAULT => 'definition|translation',
				ParamValidator::PARAM_ISMULTI => true,
				ApiBase::PARAM_HELP_MSG_PER_VALUE => [
					'translation' => [ 'apihelp-query+messagecollection-paramvalue-prop-translation', TRANSLATE_FUZZY ],
				],
			],
		];
	}

	/** @inheritDoc */
	protected function getExamplesMessages(): array {
		return [
			'action=query&meta=siteinfo&siprop=languages'
				=> 'apihelp-query+messagecollection-example-1',
			'action=query&list=messagecollection&mcgroup=page-Example'
				=> 'apihelp-query+messagecollection-example-2',
			'action=query&list=messagecollection&mcgroup=page-Example&mclanguage=fi&' .
				'mcprop=definition|translation|tags&mcfilter=optional'
				=> 'apihelp-query+messagecollection-example-3',
		];
	}
}
