<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\MessageGroupProcessing;

use Job;
use MediaWiki\Extension\Translate\PageTranslation\TranslatableBundleMover;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;

/**
 * Contains class with job for moving translation pages.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @ingroup PageTranslation JobQueue
 */
class MoveTranslatableBundleJob extends Job {
	private TranslatableBundleMover $bundleMover;

	/**
	 * @param Title $source
	 * @param Title $target
	 * @param array<string,string> $moves list of pages to be moved
	 * @param array<string,bool> $redirects a list of pages to leave redirect for
	 * @param string $reason
	 * @param User $performer
	 * @return self
	 */
	public static function newJob(
		Title $source,
		Title $target,
		array $moves,
		array $redirects,
		string $reason,
		User $performer
	): self {
		$params = [
			'source' => $source->getPrefixedText(),
			'target' => $target->getPrefixedText(),
			'moves' => $moves,
			'redirects' => $redirects,
			'summary' => $reason,
			'performer' => $performer->getName(),
		];

		return new self( $target, $params );
	}

	public function __construct( Title $title, array $params = [] ) {
		parent::__construct( 'MoveTranslatableBundleJob', $title, $params );
		$this->bundleMover = Services::getInstance()->getTranslatableBundleMover();
	}

	/** @inheritDoc */
	public function run() {
		$sourceTitle = Title::newFromText( $this->params['source'] );
		$targetTitle = Title::newFromText( $this->params['target'] );

		$userFactory = MediaWikiServices::getInstance()->getUserFactory();
		$performer = $userFactory->newFromName( $this->params['performer'] );

		$this->bundleMover->moveSynchronously(
			$sourceTitle,
			$targetTitle,
			$this->params['moves'],
			$this->params['redirects'] ?? [],
			$performer,
			$this->params['summary']
		);

		return true;
	}
}
