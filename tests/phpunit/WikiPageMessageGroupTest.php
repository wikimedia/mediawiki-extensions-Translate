<?php
/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @file
 */

use MediaWiki\Extension\Translate\MessageGroupProcessing\MessageGroups;
use MediaWiki\Extension\Translate\Validation\ValidationRunner;

/**
 * @group medium
 * @group Database
 * @covers \WikiPageMessageGroup
 */
class WikiPageMessageGroupTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();
		MessageGroups::singleton()->overrideGroupsForTesting( $this->getTestGroups() );
	}

	public function getTestGroups(): array {
		$anotherPageMessageGroup = new WikiPageMessageGroup( 'anotherpageid', 'mypage' );
		$anotherPageMessageGroup->setLabel( 'thelabel' ); // Example
		$anotherPageMessageGroup->setNamespace( 5 ); // Example

		$list['anotherpageid'] = $anotherPageMessageGroup;
		return $list;
	}

	public function testMessageValidator() {
		$group = MessageGroups::getGroup( 'anotherpageid' );
		$msgValidator = $group->getValidator();

		$this->assertInstanceOf( ValidationRunner::class, $msgValidator,
			'returns a valid object of ValidationRunner class.' );
	}
}
