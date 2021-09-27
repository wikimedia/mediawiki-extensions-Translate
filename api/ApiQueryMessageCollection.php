<?php
/**
 * Api module for querying MessageCollection.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\Extension\Translate\Utilities\ConfigHelper;

/**
 * Api module for querying MessageCollection.
 *
 * @ingroup API TranslateAPI
 */
class ApiQueryMessageCollection extends ApiQueryGeneratorBase {
	/** @var ConfigHelper */
	private $configHelper;

	public function __construct(
		ApiQuery $query,
		string $moduleName,
		ConfigHelper $configHelper
	) {
		parent::__construct( $query, $moduleName, 'mc' );
		$this->configHelper = $configHelper;
	}

	public function execute() {
		$this->run();
	}

	public function getCacheMode( $params ) {
		return 'public';
	}

	public function executeGenerator( $resultPageSet ) {
		$this->run( $resultPageSet );
	}

	private function validateLanguageCode( string $code ): void {
		if ( !TranslateUtils::isSupportedLanguageCode( $code ) ) {
			$this->dieWithError( [ 'apierror-translate-invalidlanguage', $code ] );
		}
	}

	private function run( ApiPageSet $resultPageSet = null ) {
		$params = $this->extractRequestParams();

		$group = MessageGroups::getGroup( $params['group'] );
		if ( !$group ) {
			$this->dieWithError( [ 'apierror-badparameter', 'mcgroup' ] );
		}

		$languageCode = $params[ 'language' ];
		$this->validateLanguageCode( $languageCode );

		// Even though translation to source language maybe disabled, we still want to
		// fetch the message collections for the source language.
		if ( $group->getSourceLanguage() === $languageCode ) {
			$name = Language::fetchLanguageName( $languageCode, $this->getLanguage()->getCode() );
			$this->addWarning( [ 'apiwarn-translate-language-disabled-source', wfEscapeWikiText( $name ) ] );
		} else {
			$languages = $group->getTranslatableLanguages();
			if ( $languages === null ) {
				$checks = [
					$group->getId(),
					strtok( $group->getId(), '-' ),
					'*'
				];

				$disabledLanguages = $this->configHelper->getDisabledTargetLanguages();
				foreach ( $checks as $check ) {
					if ( isset( $disabledLanguages[ $check ][ $languageCode ] ) ) {
						$name = Language::fetchLanguageName( $languageCode, $this->getLanguage()->getCode() );
						$reason = $disabledLanguages[ $check ][ $languageCode ];
						$this->dieWithError( [ 'apierror-translate-language-disabled-reason', $name, $reason ] );
					}
				}
			} elseif ( !isset( $languages[ $languageCode ] ) ) {
				// Not a translatable language
				$name = Language::fetchLanguageName( $languageCode, $this->getLanguage()->getCode() );
				$this->dieWithError( [ 'apierror-translate-language-disabled', $name ] );
			}
		}

		if ( MessageGroups::isDynamic( $group ) ) {
			/** @var RecentMessageGroup $group */
			// @phan-suppress-next-line PhanUndeclaredMethod
			$group->setLanguage( $params['language'] );
		}

		$messages = $group->initCollection( $params['language'] );

		foreach ( $params['filter'] as $filter ) {
			$value = null;
			if ( strpos( $filter, ':' ) !== false ) {
				list( $filter, $value ) = explode( ':', $filter, 2 );
			}
			/* The filtering params here are swapped wrt MessageCollection.
			 * There (fuzzy) means do not show fuzzy, which is the same as !fuzzy
			 * here and fuzzy here means (fuzzy, false) there. */
			try {
				if ( $filter[0] === '!' ) {
					$messages->filter( substr( $filter, 1 ), true, $value );
				} else {
					$messages->filter( $filter, false, $value );
				}
			} catch ( MWException $e ) {
				$this->dieWithError(
					[ 'apierror-translate-invalidfilter', wfEscapeWikiText( $e->getMessage() ) ],
					'invalidfilter'
				);
			}
		}

		$resultSize = count( $messages );
		$offsets = $messages->slice( $params['offset'], $params['limit'] );
		$batchSize = count( $messages );
		list( /*$backwardsOffset*/, $forwardsOffset, $startOffset ) = $offsets;

		$result = $this->getResult();
		$result->addValue(
			[ 'query', 'metadata' ],
			'state',
			self::getWorkflowState( $group->getId(), $params['language'] )
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

	/**
	 * @param ApiResult $result
	 * @param array $props
	 * @param TMessage $message
	 * @return array
	 */
	public function extractMessageData( $result, $props, $message ) {
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
		// BC
		if ( isset( $props['revision'] ) ) {
			$data['revision'] = $message->getProperty( 'revision' );
		}
		if ( isset( $props['properties'] ) ) {
			foreach ( $message->getPropertyNames() as $prop ) {
				$data['properties'][$prop] = $message->getProperty( $prop );
				ApiResult::setIndexedTagNameRecursive( $data['properties'], 'val' );
			}
		}

		return $data;
	}

	/**
	 * Get the current workflow state for the message group for the given language
	 *
	 * @param string $groupId Group id.
	 * @param string $language Language tag.
	 * @return string|bool State id or false.
	 */
	protected static function getWorkflowState( $groupId, $language ) {
		$dbr = wfGetDB( DB_REPLICA );

		return $dbr->selectField(
			'translate_groupreviews',
			'tgr_state',
			[
				'tgr_group' => $groupId,
				'tgr_lang' => $language
			],
			__METHOD__
		);
	}

	protected function getAllowedParams() {
		return [
			'group' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			],
			'language' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => 'en',
			],
			'limit' => [
				ApiBase::PARAM_DFLT => 500,
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_BIG2,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_BIG2,
			],
			'offset' => [
				ApiBase::PARAM_DFLT => '',
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			],
			'filter' => [
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '!optional|!ignored',
				ApiBase::PARAM_ISMULTI => true,
			],
			'prop' => [
				ApiBase::PARAM_TYPE => [
					'definition',
					'translation',
					'tags',
					'revision',
					'properties'
				],
				ApiBase::PARAM_DFLT => 'definition|translation',
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_HELP_MSG =>
					[ 'apihelp-query+messagecollection-param-prop', TRANSLATE_FUZZY ],
			],
		];
	}

	protected function getExamplesMessages() {
		return [
			'action=query&meta=siteinfo&siprop=languages'
				=> 'apihelp-query+messagecollection-example-1',
			'action=query&list=messagecollection&mcgroup=page-Example'
				=> 'apihelp-query+messagecollection-example-2',
			'action=query&list=messagecollection&mcgroup=page-Example&mclanguage=fi&' .
				'mcprop=definition|translation|tags&mcfilter=optional'
				=> 'apihelp-query+messagecollection-example-3',
			'action=query&generator=messagecollection&gmcgroup=page-Example&gmclanguage=nl&prop=revisions'
				=> 'apihelp-query+messagecollection-example-4',
		];
	}
}
