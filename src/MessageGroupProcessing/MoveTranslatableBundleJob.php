<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Job;
use MediaWiki\Extension\Translate\PageTranslation\TranslatableBundleMover;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\MediaWikiServices;
use RequestContext;
use Title;
use User;
use Wikimedia\ScopedCallback;

/**
 * Contains class with job for moving translation pages.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup PageTranslation JobQueue
 */
class MoveTranslatableBundleJob extends Job {
	/** @var TranslatableBundleMover */
	private $bundleMover;

	public static function newJob(
		Title $source,
		Title $target,
		array $moves,
		string $summary,
		User $performer,
		array $session
	): self {
		$params = [
			'source' => $source->getPrefixedText(),
			'target' => $target->getPrefixedText(),
			'moves' => $moves,
			'summary' => $summary,
			'performer' => $performer->getName(),
			'session' => $session
		];

		$self = new self( $target, $params );

		return $self;
	}

	public function __construct( Title $title, array $params = [] ) {
		parent::__construct( 'MoveTranslatableBundleJob', $title, $params );
		$this->bundleMover = Services::getInstance()->getTranslatableBundleMover();
	}

	public function run() {
		$sourceTitle = Title::newFromText( $this->params['source'] );
		$targetTitle = Title::newFromText( $this->params['target'] );

		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		$performer = $userFactory->newFromName( $this->params['performer'] );

		// Restore the session information if present
		if ( isset( $this->params[ 'session' ] ) ) {
			$scope = RequestContext::importScopedSession( $this->params['session'] );
			$this->addTeardownCallback( static function () use ( &$scope ) {
				ScopedCallback::consume( $scope );
			} );
		}

		$this->bundleMover->moveSynchronously(
			$sourceTitle,
			$targetTitle,
			$this->params['moves'],
			$performer,
			$this->params['summary']
		);

		return true;
	}
}
