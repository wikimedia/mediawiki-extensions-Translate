<?php

use MediaWiki\Revision\RevisionRecord;

/**
 * @group Database
 * @covers TranslateUtils
 */
class TranslateUtilsTest extends MediaWikiIntegrationTestCase {

	/**
	 * Creates a new page with name and text, returns a revision
	 * @param string $name
	 * @param string $text
	 * @return RevisionRecord
	 * @throws MWException
	 */
	private function createPageWithNameAndText( $name, $text, $user ) {
		$status = $this->editPage( $name, $text, '', NS_MAIN, $user );
		$this->assertTrue( $status->isOK() );
		return $status->getValue()['revision-record'];
	}

	/**
	 * @covers TranslateUtils::getContents
	 * @throws MWException
	 */
	public function testGetContents() {
		$user = $this->getTestUser()->getUser();
		$title1 = __METHOD__ . '_Page1';
		$this->createPageWithNameAndText( $title1, $title1 . 'TEXT', $user );
		$title2 = __METHOD__ . '_Page2';
		$this->createPageWithNameAndText( $title2, $title2 . 'TEXT', $user );
		$result = TranslateUtils::getContents( [ $title1, $title2, 'Does_Not_Exist' ], NS_MAIN );
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
