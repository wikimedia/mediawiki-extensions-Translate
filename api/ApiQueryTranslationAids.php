<?php
/**
 * Api module for querying message aids.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license GPL-2.0+
 */

/**
 * Api module for querying message aids.
 *
 * @ingroup API TranslateAPI
 */
class ApiTranslationAids extends ApiBase {
	public function execute() {
		$params = $this->extractRequestParams();

		$title = Title::newFromText( $params['title'] );
		if ( !$title ) {
			$this->dieUsage( 'Invalid title', 'invalidtitle' );
		}

		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			$this->dieUsage( 'Title does not correspond to a translatable message', 'nomessagefortitle' );
		}

		if ( strval( $params['group'] ) !== '' ) {
			$group = MessageGroups::getGroup( $params['group'] );
		} else {
			$group = $handle->getGroup();
		}

		if ( !$group ) {
			$this->dieUsage( 'Invalid group', 'invalidgroup' );
		}

		$data = array();
		$times = array();

		$props = $params['prop'];

		$types = $group->getTranslationAids();
		$result = $this->getResult();
		foreach ( $props as $type ) {
			// Do not proceed if translation aid is not supported for this message group
			if ( !isset( $types[$type] ) ) {
				continue;
			}

			$start = microtime( true );
			$class = $types[$type];
			$obj = new $class( $group, $handle, $this );

			try {
				$aid = $obj->getData();
			} catch ( TranslationHelperException $e ) {
				$aid = array( 'error' => $e->getMessage() );
			}

			if ( isset( $aid['**'] ) ) {
				$result->setIndexedTagName( $aid, $aid['**'] );
				unset( $aid['**'] );
			}

			$data[$type] = $aid;
			$times[$type] = round( microtime( true ) - $start, 3 );
		}

		$result->addValue( null, 'helpers', $data );
		$result->addValue( null, 'times', $times );
	}

	public function getAllowedParams() {
		$props = array_keys( TranslationAid::getTypes() );
		wfRunHooks( 'TranslateTranslationAids', array( &$props ) );

		return array(
			'title' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'group' => array(
				ApiBase::PARAM_TYPE => 'string',
			),
			'prop' => array(
				ApiBase::PARAM_DFLT => implode( '|', $props ),
				ApiBase::PARAM_TYPE => $props,
				ApiBase::PARAM_ISMULTI => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'title' => 'Full title of a known message',
			'group' => 'Message group the message belongs to. If empty then primary group is used.',
			'prop' => 'Which translation helpers to include.',
		);
	}

	public function getDescription() {
		return 'Query all translations aids';
	}

	public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
			array( 'code' => 'invalidtitle', 'info' => 'The given title is invalid' ),
			array( 'code' => 'invalidgroup', 'info' => 'The given or guessed group is invalid' ),
			array( 'code' => 'nomessagefortitle', 'info' => 'Title does not correspond to a translatable message' ),
		) );
	}

	protected function getExamples() {
		return array(
			"api.php?action=translationaids&title=MediaWiki:January/fi",
		);
	}

	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}
}
