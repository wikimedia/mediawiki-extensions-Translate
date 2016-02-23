<?php
/**
 * Api module for querying MessageCollection.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Api module for querying MessageCollection.
 *
 * @ingroup API TranslateAPI
 */
class ApiQueryMessageCollection extends ApiQueryGeneratorBase {

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'mc' );
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

	private function run( ApiPageSet $resultPageSet = null ) {
		$params = $this->extractRequestParams();

		$group = MessageGroups::getGroup( $params['group'] );
		if ( !$group ) {
			$this->dieUsageMsg( array( 'missingparam', 'mcgroup' ) );
		}

		if ( MessageGroups::isDynamic( $group ) ) {
			/**
			 * @var RecentMessageGroup $group
			 */
			$group->setLanguage( $params['language'] );
		}

		$result = $this->getResult();

		$languages = $group->getTranslatableLanguages();

		if ( $languages !== null && !isset( $languages[$params['language']] ) ) {
			$this->dieUsage(
				'Translation to this language is disabled',
				'translate-language-disabled'
			);
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
				$this->dieUsage( $e->getMessage(), 'invalidfilter' );
			}
		}

		$resultSize = count( $messages );
		$offsets = $messages->slice( $params['offset'], $params['limit'] );
		$batchSize = count( $messages );
		list( /*$backwardsOffset*/, $forwardsOffset, $startOffset ) = $offsets;

		$result->addValue(
			array( 'query', 'metadata' ),
			'state',
			self::getWorkflowState( $group->getId(), $params['language'] )
		);

		$result->addValue( array( 'query', 'metadata' ), 'resultsize', $resultSize );
		$result->addValue(
			array( 'query', 'metadata' ),
			'remaining',
			$resultSize - $startOffset - $batchSize
		);

		$messages->loadTranslations();

		$pages = array();

		if ( $forwardsOffset !== false ) {
			$this->setContinueEnumParameter( 'offset', $forwardsOffset );
		}

		$props = array_flip( $params['prop'] );

		/** @var Title $title */
		foreach ( $messages->keys() as $mkey => $title ) {
			if ( is_null( $resultPageSet ) ) {
				$data = $this->extractMessageData( $result, $props, $messages[$mkey] );
				$data['title'] = $title->getPrefixedText();

				$result->addValue( array( 'query', $this->getModuleName() ), null, $data );
			} else {
				$pages[] = $title;
			}
		}

		if ( is_null( $resultPageSet ) ) {
			$result->addIndexedTagName(
				array( 'query', $this->getModuleName() ),
				'message'
			);
		} else {
			$resultPageSet->populateFromTitles( $pages );
		}
	}

	/**
	 * @param $result ApiResult
	 * @param $props array
	 * @param $message ThinMessage
	 * @return array
	 */
	public function extractMessageData( $result, $props, $message ) {
		$data['key'] = $message->key();

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
		$dbr = wfGetDB( DB_SLAVE );

		return $dbr->selectField(
			'translate_groupreviews',
			'tgr_state',
			array(
				'tgr_group' => $groupId,
				'tgr_lang' => $language
			),
			__METHOD__
		);
	}

	public function getAllowedParams() {
		return array(
			'group' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'language' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => 'en',
			),
			'limit' => array(
				ApiBase::PARAM_DFLT => 500,
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_BIG2,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_BIG2,
			),
			'offset' => array(
				ApiBase::PARAM_DFLT => '',
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			),
			'filter' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '!optional|!ignored',
				ApiBase::PARAM_ISMULTI => true,
			),
			'prop' => array(
				ApiBase::PARAM_TYPE => array(
					'definition',
					'translation',
					'tags',
					'revision',
					'properties'
				),
				ApiBase::PARAM_DFLT => 'definition|translation',
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_HELP_MSG =>
					array( 'apihelp-query+messagecollection-param-prop', '!!FUZZY!!' ),
			),
		);
	}

	protected function getExamplesMessages() {
		return array(
			'action=query&meta=siteinfo&siprop=languages'
				=> 'apihelp-query+messagecollection-example-1',
			'action=query&list=messagecollection&mcgroup=page-Example'
				=> 'apihelp-query+messagecollection-example-2',
			'action=query&list=messagecollection&mcgroup=page-Example&mclanguage=fi&' .
				'mcprop=definition|translation|tags&mcfilter=optional'
				=> 'apihelp-query+messagecollection-example-3',
			'action=query&generator=messagecollection&gmcgroup=page-Example&gmclanguage=nl&prop=revisions'
				=> 'apihelp-query+messagecollection-example-4',
		);
	}
}
