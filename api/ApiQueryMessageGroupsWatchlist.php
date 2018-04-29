<?php
/**
 * Api module for querying watched message groups.
 *
 * @ingroup API TranslateAPI
 */
class ApiQueryMessageGroupsWatchlist extends ApiQueryBase {

	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'mgw' );
	}

	public function execute() {
		$user = $this->getUser();

		if ( !$user->isLoggedIn() ) {
			$this->dieWithError( 'translate-groupwatchlistanontext', 'notloggedin' );
		}

		$userId = $user->getId();
		$msgGroups = $this->getWatchlist( $userId );
		$this->getResult()->addValue( [ 'query', 'groups' ], null, $msgGroups );;
	}

	private function getWatchlist( $userId ) {
		$dbw = wfGetDB( DB_MASTER );
		$table = 'translate_groupwatchlist';
		$field = 'tgw_group';
		$cond = [ 'tgw_user' => $userId ];
		return $dbw->selectField( $table, $field, $cond, __METHOD__ );
	}

	public function getAllowedParams() {
		return [
			'continue' => [
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			],
		];
	}

	protected function getExamplesMessages() {
		return [
			'action=query&format=json&meta=messagegroupswatchlist'
			=> 'apihelp-query+messagegroupswatchlist'
		];
	}
}
