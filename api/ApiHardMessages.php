<?php
/**
 * API module for marking translations hard
 * @file
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012-2013, Santhosh Thottingal
 * @license GPL-2.0+
 */

/**
 * API module for marking translations hard
 * Records the skip count for the message.
 * Assumption: the more the translators skip a message, the more hard it is to translate.
 *
 * @since 2012-12-20
 * @ingroup API TranslateAPI
 */
class ApiHardMessages extends ApiBase {
	protected static $right = 'translate';

	public function execute() {
		if ( !$this->getUser()->isAllowed( self::$right ) ) {
			$this->dieUsage( 'Permission denied', 'permissiondenied' );
		}

		$params = $this->extractRequestParams();
		$title = Title::newFromText( $params['title'] );

		if ( !$title ) {
			$this->dieUsage( 'Invalid title', 'invalidtitle' );
		}

		$handle = new MessageHandle( $title );
		if ( !$handle->isValid() ) {
			$this->dieUsage( 'Invalid title', 'invalidtitle' );
		}

		$baseTitle = Title::makeTitle( $title->getNamespace(),
			$handle->getKey() . '/' . $handle->getGroup()->getSourceLanguage() );
		$revision = Revision::newFromTitle( $baseTitle );

		if ( !$revision ) {
			// This can fail. See https://bugzilla.wikimedia.org/show_bug.cgi?id=43286
			$this->dieUsage( 'Invalid revision', 'invalidrevision' );
		}

		$count = self::getHardCount( $revision ) + 1;
		self::doMarkHard( $revision, $count );

		$output = array(
			'title' => $baseTitle->getPrefixedText(),
			'pageid' => $revision->getPage(),
			'revision' => $revision->getId(),
			'count' => $count
		);

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	/**
	 * Mark the message skip count
	 * @param Revision $revision
	 * @param int $count
	 */
	public static function doMarkHard( Revision $revision, $count ) {
		$dbw = wfGetDB( DB_MASTER );
		$table = 'revtag';

		if ( $count === 1 ) {
			$row = array(
				'rt_type' => 'hard',
				'rt_page' => $revision->getPage(),
				'rt_revision' => $revision->getId(),
				'rt_value' => $count
			);
			$options = array( 'IGNORE' );
			$dbw->insert( $table, $row, __METHOD__, $options );
		} else {
			$dbw->update( $table,
				array(
					'rt_value' => $count
				),
				array(
					'rt_type' => 'hard',
					'rt_page' => $revision->getPage(),
				),
				__METHOD__
			);
		}
	}

	/**
	 * Get the number of times the message was skipped
	 * @param Revision $revision
	 *
	 * @return int How many times the message was skipped
	 */
	public static function getHardCount( Revision $revision ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->selectField(
			'revtag',
			'rt_value',
			array( 'rt_type = "hard"',
				'rt_page = ' . $revision->getPage(),
			),
			__METHOD__
		);

		$count = intval( $res );

		return $count;
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return 'csrf';
	}

	public function getAllowedParams() {
		return array(
			'title' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'token' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getParamDescription() {
		$action = TranslateUtils::getTokenAction( 'hardmessages' );

		return array(
			'title' => 'The title of the message to mark hard',
			'token' => "A token previously acquired with $action",
		);
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getDescription() {
		return 'Mark translations hard';
	}

	/**
	 * @deprecated since MediaWiki core 1.25
	 */
	public function getExamples() {
		return array(
			'api.php?action=hardmessages&title=SampleTitle&token=foo',
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=hardmessages&title=SampleTitle&token=foo'
				=> 'apihelp-hardmessages-example-1',
		);
	}

	// These two functions implement pre-1.24 token fetching via the
	// ApiTokensGetTokenTypes hook, kept for backwards compatibility.
	public static function getToken() {
		$user = RequestContext::getMain()->getUser();
		if ( !$user->isAllowed( self::$right ) ) {
			return false;
		}

		return $user->getEditToken();
	}

	public static function injectTokenFunction( &$list ) {
		$list['hardmessage'] = array( __CLASS__, 'getToken' );

		return true;
	}

	public static function getRight() {
		return self::$right;
	}
}
