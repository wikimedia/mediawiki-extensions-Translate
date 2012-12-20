<?php
/**
 * API module for marking translations hard
 * @file
 * @author Santhosh Thottingal
 * @copyright Copyright © 2012, Santhosh Thottingal
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * API module for marking translations hard
 * Records the skip count for the message.
 * Assumption: the more the translators skip a message, the more hard it is to translate.
 *
 * @ingroup API TranslateAPI
 */
class ApiHardMessages extends ApiBase {
	protected static $right = 'translate';

	public function execute() {
		if ( !$this->getUser()->isallowed( self::$right ) ) {
			$this->dieUsage( 'Permission denied', 'permissiondenied' );
		}

		$params = $this->extractRequestParams();

		$revision = Revision::newFromTitle( Title::newFromText( $params['title']  ) );
		if ( !$revision ) {
			$this->dieUsage( 'Invalid title', 'invalidtitle' );
		}

		$count = self::getHardCount(  $revision ) + 1;
		wfDebugLog( 'myextension', 'Something is not right: ' . print_r( $count, true ) );
		self::doMarkHard( $revision, $count );

		$output = array( 'hardmessage' => array(
			'title' => $revision->getTitle()->getPrefixedText(),
			'pageid' => $revision->getPage(),
			'revision' => $revision->getId(),
			'count' => $count
		) );

		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	/**
	 * Mark the message skip count
	 * @param Revision $revision
	 * @param int $count
	 */
	public static function doMarkHard ( Revision $revision, $count ) {
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
				array( 'rt_type = "hard"',
					'rt_page = ' . $revision->getPage(),
				),
				__METHOD__
			);
		}
	}

	/**
	 * Validates review action by checking permissions and other things.
	 * @param Revision $revision
	 * @since 2012-12-20
	 * @return How many times the message was skipped
	 */
	public static function getHardCount(  Revision $revision ) {
		$count = 0;
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'revtag',
			'rt_value',
			array( 'rt_type = "hard"',
				'rt_page = ' . $revision->getPage(),
			),
			__METHOD__
		);

		foreach ( $res as $row ) {
			$count = $row->rt_value;
		}

		return $count;
	}

	public function isWriteMode() {
		return true;
	}

	public function needsToken() {
		return true;
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

	public function getParamDescription() {
		$action = TranslateUtils::getTokenAction( 'hardmessages' );
		return array(
			'title' => 'The title of the message to mark hard',
			'token' => "A token previously acquired with $action",
		);
	}

	public function getDescription() {
		return 'Mark translations hard';
	}

	public function getPossibleErrors() {
		$right = self::$right;
		return array_merge( parent::getPossibleErrors(), array(
			array( 'code' => 'permissiondenied', 'info' => "You must have $right right" ),
			array( 'code' => 'invalidtitle', 'info' => 'Title $1 is invalid' ),
		) );
	}

	public function getExamples() {
		return array(
			'api.php?action=hardmessages&revision=1&token=foo',
		);
	}

	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}

	public static function getToken() {
		global $wgUser;
		if ( !$wgUser->isAllowed( self::$right ) ) {
			return false;
		}

		return $wgUser->getEditToken();
	}

	public static function injectTokenFunction( &$list ) {
		$list['hardmessage'] = array( __CLASS__, 'getToken' );
		return true;
	}

	public static function getRight() {
		return self::$right;
	}

}
