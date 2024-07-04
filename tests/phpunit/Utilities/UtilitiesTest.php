<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Utilities;

use MediaWiki\Revision\RevisionRecord;
use MediaWiki\User\User;
use MediaWikiIntegrationTestCase;

/**
 * @group Database
 * @coversDefaultClass \MediaWiki\Extension\Translate\Utilities\Utilities
 */
class UtilitiesTest extends MediaWikiIntegrationTestCase {

	/**
	 * Creates a new page with name and text, returns a revision
	 */
	private function createPageWithNameAndText( string $name, string $text, User $user ): RevisionRecord {
		$status = $this->editPage( $name, $text, '', NS_MAIN, $user );
		$this->assertTrue( $status->isOK() );
		return $status->getValue()['revision-record'];
	}

	/** @covers ::getContents */
	public function testGetContents() {
		$user = $this->getTestUser()->getUser();
		$title1 = __METHOD__ . '_Page1';
		$this->createPageWithNameAndText( $title1, $title1 . 'TEXT', $user );
		$title2 = __METHOD__ . '_Page2';
		$this->createPageWithNameAndText( $title2, $title2 . 'TEXT', $user );
		$result = Utilities::getContents( [ $title1, $title2, 'Does_Not_Exist' ], NS_MAIN );
		$this->assertEquals( [
			$title1 => [
				$title1 . 'TEXT',
				$user->getName()
			],
			$title2 => [
				$title2 . 'TEXT',
				$user->getName()
			]
		], $result );
	}
}
