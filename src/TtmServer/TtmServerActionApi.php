<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TtmServer;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiMain;
use MediaWiki\Config\Config;
use MediaWiki\Config\ServiceOptions;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module for TtmServer
 * @ingroup API TranslateAPI TTMServer
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2012-01-26
 */
class TtmServerActionApi extends ApiBase {
	/** @var TtmServerFactory */
	private $ttmServerFactory;
	/** @var ServiceOptions */
	private $options;

	private const CONSTRUCTOR_OPTIONS = [
		'LanguageCode',
		'TranslateTranslationDefaultService',
		'TranslateTranslationServices',
	];

	public function __construct(
		ApiMain $mainModule,
		string $moduleName,
		TtmServerFactory $ttmServerFactory,
		Config $config
	) {
		parent::__construct( $mainModule, $moduleName );
		$this->ttmServerFactory = $ttmServerFactory;
		$this->options = new ServiceOptions( self::CONSTRUCTOR_OPTIONS, $config );
	}

	public function execute(): void {
		if ( !$this->getAvailableTranslationServices() ) {
			$this->dieWithError( 'apierror-translate-notranslationservices' );
		}

		$params = $this->extractRequestParams();

		$server = $this->ttmServerFactory->create( $params[ 'service' ] );
		if ( !$server instanceof ReadableTtmServer ) {
			$this->dieWithError( 'apierror-translate-notranslationservices' );
		}

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

		$result->addIndexedTagName( $this->getModuleName(), 'suggestion' );
	}

	private function getAvailableTranslationServices(): array {
		$translationServices = $this->options->get( 'TranslateTranslationServices' );

		$good = [];
		foreach ( $translationServices as $id => $config ) {
			$public = $config['public'] ?? false;
			if ( $config['type'] === 'ttmserver' && $public ) {
				$good[] = $id;
			}
		}

		return $good;
	}

	protected function getAllowedParams(): array {
		$available = $this->getAvailableTranslationServices();

		$ret = [
			'service' => [
				ParamValidator::PARAM_TYPE => $available,
			],
			'sourcelanguage' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'targetlanguage' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'text' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
		];

		if ( $available ) {
			// Don't add this if no services are available, it makes
			// ApiStructureTest unhappy
			$ret['service'][ParamValidator::PARAM_DEFAULT] =
				$this->options->get( 'TranslateTranslationDefaultService' );
		}

		return $ret;
	}

	protected function getExamplesMessages(): array {
		return [
			'action=ttmserver&sourcelanguage=en&targetlanguage=fi&text=Help'
				=> 'apihelp-ttmserver-example-1',
		];
	}
}
