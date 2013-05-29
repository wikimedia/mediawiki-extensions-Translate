<?php
/**
 * Api module for querying MessageCollection.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2010-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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

	/**
	 * @param $resultPageSet ApiPageSet
	 */
	private function run( $resultPageSet = null ) {
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
			$this->dieUsage( 'Translation to this language is disabled', 'translate-language-disabled' );
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
			if ( $filter[0] === '!' ) {
				$messages->filter( substr( $filter, 1 ), true, $value );
			} else {
				$messages->filter( $filter, false, $value );
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
		$result->addValue( array( 'query', 'metadata' ), 'remaining', $resultSize - $startOffset - $batchSize );

		$messages->loadTranslations();

		$pages = array();

		if ( $forwardsOffset !== false ) {
			$this->setContinueEnumParameter( 'offset', $forwardsOffset );
		}

		$props = array_flip( $params['prop'] );

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
			$result->setIndexedTagName_internal( array( 'query', $this->getModuleName() ), 'message' );
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
				$result->setIndexedTagName_recursive( $data['properties'], 'val' );
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
				ApiBase::PARAM_MAX => ApiBase::LIMIT_BIG1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_BIG2
			),
			'offset' => array(
				ApiBase::PARAM_DFLT => '',
				ApiBase::PARAM_TYPE => 'string',
			),
			'filter' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_DFLT => '!optional|!ignored',
				ApiBase::PARAM_ISMULTI => true,
			),
			'prop' => array(
				ApiBase::PARAM_TYPE => array( 'definition', 'translation', 'tags', 'revision', 'properties' ),
				ApiBase::PARAM_DFLT => 'definition|translation',
				ApiBase::PARAM_ISMULTI => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'group' => 'Message group',
			'language' => 'Language code',
			'offset' => 'Integer or key offset for start',
			'limit' => 'How many messages to show (after filtering)',
			'prop' => array(
				'Which properties to get',
				'definition  - message definition',
				'translation - current translation (without !!FUZZY!! string if any, use the tags to check for outdated or broken translations)',
				'tags        - message tags, like optional, ignored and fuzzy',
				'properties  - message properties, like status, revision, last-translator. Can vary between messages.',
				'revision    - deprecated! use properties!',
			),
			'filter' => array(
				'Message collection filters. Use ! to negate condition. For example !fuzzy means list only all non-fuzzy messages. Filters are applied in the order given.',
				'fuzzy             - messages with fuzzy tag',
				'optional          - messages which should be translated only if changes are necessary',
				'ignored           - messages which are never translated',
				'hastranslation    - messages which have a translation regardless if it is fuzzy or not',
				'translated        - messages which have a translation which is not fuzzy',
				'changed           - messages which has been translated or changed since last export',
				'reviewer:#        - messages where given userid # is among reviewers',
				'last-translator:# - messages where given userid # is the last translator',
			),
		);
	}

	public function getDescription() {
		return 'Query MessageCollection about translations';
	}

	protected function getExamples() {
		$group = 'page-Example';

		return array(
			'api.php?action=query&meta=siteinfo&siprop=languages List of supported languages',
			"api.php?action=query&list=messagecollection&mcgroup=$group List of non-optional message definitions for group $group",
			"api.php?action=query&list=messagecollection&mcgroup=$group&mclanguage=fi&mcprop=definition|translation|tags&mcfilter=optional List of optional messages in Finnish with tags for group $group",
			"api.php?action=query&generator=messagecollection&gmcgroup=$group&gmclanguage=nl&prop=revisions More information about latest translation revisions for group $group",
		);
	}

	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}
}
