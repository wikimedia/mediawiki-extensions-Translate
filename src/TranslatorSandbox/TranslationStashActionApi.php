<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use ApiBase;
use ApiMain;
use FormatJson;
use MediaWiki\User\UserFactory;
use MessageGroups;
use MessageHandle;
use MessageIndex;
use Title;
use Wikimedia\ParamValidator\ParamValidator;
use Wikimedia\Rdbms\ILoadBalancer;

/**
 * WebAPI module for storing translations for users who are in a sandbox.
 * Access is controlled by hooks in TranslateSandbox class.
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2013.06
 */
class TranslationStashActionApi extends ApiBase {
	/** @var ILoadBalancer */
	private $loadBalancer;
	/** @var UserFactory */
	private $userFactory;

	public function __construct(
		ApiMain $mainModule,
		string $moduleName,
		ILoadBalancer $loadBalancer,
		UserFactory $userFactory
	) {
		parent::__construct( $mainModule, $moduleName );
		$this->loadBalancer = $loadBalancer;
		$this->userFactory = $userFactory;
	}

	public function execute(): void {
		$params = $this->extractRequestParams();

		// The user we are operating on, not necessarly the user making the request
		$user = $this->getUser();

		if ( isset( $params['username'] ) ) {
			if ( $this->getUser()->isAllowed( 'translate-sandboxmanage' ) ) {
				$user = $this->userFactory->newFromName( $params['username'] );
				if ( !$user ) {
					$this->dieWithError( [ 'apierror-badparameter', 'username' ], 'invalidparam' );
				}
			} else {
				$this->dieWithError( [ 'apierror-badparameter', 'username' ], 'invalidparam' );
			}
		}

		$stash = new TranslationStashStorage( $this->loadBalancer->getConnection( DB_PRIMARY ) );
		$action = $params['subaction'];

		if ( $action === 'add' ) {
			if ( !isset( $params['title'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'title' ] );
			}
			if ( !isset( $params['translation'] ) ) {
				$this->dieWithError( [ 'apierror-missingparam', 'translation' ] );
			}

			// @todo: Return value of Title::newFromText not checked
			$translation = new StashedTranslation(
				$user,
				Title::newFromText( $params['title'] ),
				$params['translation'],
				FormatJson::decode( $params['metadata'], true )
			);
			$stash->addTranslation( $translation );
		}

		$output = [];
		if ( $action === 'query' ) {
			$output['translations'] = [];

			$translations = $stash->getTranslations( $user );
			foreach ( $translations as $translation ) {
				$output['translations'][] = $this->formatTranslation( $translation );
			}
		}

		// If we got this far, nothing has failed
		$output['result'] = 'ok';
		$this->getResult()->addValue( null, $this->getModuleName(), $output );
	}

	private function formatTranslation( StashedTranslation $translation ): array {
		$title = $translation->getTitle();
		$handle = new MessageHandle( $title );

		// Prepare for the worst
		$definition = '';
		$comparison = '';
		if ( $handle->isValid() ) {
			$groupId = MessageIndex::getPrimaryGroupId( $handle );
			$group = MessageGroups::getGroup( $groupId );

			$key = $handle->getKey();

			$definition = $group->getMessage( $key, $group->getSourceLanguage() );
			$comparison = $group->getMessage( $key, $handle->getCode() );
		}

		return [
			'title' => $title->getPrefixedText(),
			'definition' => $definition,
			'translation' => $translation->getValue(),
			'comparison' => $comparison,
			'metadata' => $translation->getMetadata(),
		];
	}

	public function isWriteMode(): bool {
		return true;
	}

	public function needsToken(): string {
		return 'csrf';
	}

	protected function getAllowedParams(): array {
		return [
			'subaction' => [
				ParamValidator::PARAM_TYPE => [ 'add', 'query' ],
				ParamValidator::PARAM_REQUIRED => true,
			],
			'title' => [
				ParamValidator::PARAM_TYPE => 'string',
			],
			'translation' => [
				ParamValidator::PARAM_TYPE => 'string',
			],
			'metadata' => [
				ParamValidator::PARAM_TYPE => 'string',
			],
			'token' => [
				ParamValidator::PARAM_TYPE => 'string',
				ParamValidator::PARAM_REQUIRED => true,
			],
			'username' => [
				ParamValidator::PARAM_TYPE => 'string',
			],
		];
	}

	protected function getExamplesMessages(): array {
		return [
			'action=translationstash&subaction=add&title=MediaWiki:Jan/fi&translation=tammikuu&metadata={}'
				=> 'apihelp-translationstash-example-1',
			'action=translationstash&subaction=query'
				=> 'apihelp-translationstash-example-2',
		];
	}
}
