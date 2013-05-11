<?php
/**
 * API module for TTMServer
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012-2013, Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * API module for TTMServer
 *
 * @ingroup API TranslateAPI TTMServer
 * @since 2012-01-26
 */
class ApiTTMServer extends ApiBase {

	public function execute() {
		global $wgTranslateTranslationServices;
		$params = $this->extractRequestParams();

		$config = $wgTranslateTranslationServices[$params['service']];
		$server = TTMServer::factory( $config );

		$suggestions = $server->query(
			$params['sourcelanguage'],
			$params['targetlanguage'],
			$params['text']
		);

		$result = $this->getResult();
		foreach ( $suggestions as $sug ) {
			$sug['location'] = $server->expandLocation( $sug );
			unset( $sug['wiki'] );
			$result->addValue( $this->getModuleName(), null, $sug );
		}

		$result->setIndexedTagName_internal( $this->getModuleName(), 'suggestion' );
	}

	protected function getAvailableTranslationServices() {
		global $wgTranslateTranslationServices;

		$good = array();
		foreach ( $wgTranslateTranslationServices as $id => $config ) {
			if ( isset( $config['public'] ) && $config['public'] === true ) {
				$good[] = $id;
			}
		}

		return $good;
	}

	public function getAllowedParams() {
		$available = $this->getAvailableTranslationServices();

		return array(
			'service' => array(
				ApiBase::PARAM_TYPE => $available,
				ApiBase::PARAM_DFLT => 'TTMServer',
			),
			'sourcelanguage' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'targetlanguage' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
			'text' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'service' => 'Which of the available translation services to use.',
			'sourcelanguage' => 'A language code of the source text',
			'targetlanguage' => 'A language code of the suggestion',
			'text' => 'The text to find suggestions for',
		);
	}

	public function getDescription() {
		return 'Query suggestions from translation memories';
	}

	public function getExamples() {
		return array(
			'api.php?action=ttmserver&sourcelanguage=en&targetlanguage=fi&text=Help',
		);
	}

	public function getVersion() {
		return __CLASS__ . ': ' . TRANSLATE_VERSION;
	}
}
