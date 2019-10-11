<?php
/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @file
 */

 /**
  * @group medium
  */
class WikiPageMessageGroupTest extends MediaWikiTestCase {
	protected function setUp() : void {
		parent::setUp();

		$this->setTemporaryHook(
			'TranslatePostInitGroups',
			function ( &$list ) {
				$anotherPageMessageGroup = new WikiPageMessageGroup( 'anotherpageid', 'mypage' );
				$anotherPageMessageGroup->setLabel( 'thelabel' ); // Example
				$anotherPageMessageGroup->setNamespace( 5 ); // Example

				$list['anotherpageid'] = $anotherPageMessageGroup;

				return false;
			}
		);

		$this->setTemporaryHook( 'TranslateInitGroupLoaders', [] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => wfGetCache( 'hash' ) ] ) );
		$mg->recache();
	}

	public function testMessageValidator() {
		$group = MessageGroups::getGroup( 'anotherpageid' );
		$msgValidator = $group->getValidator();

		$this->assertInstanceOf( MessageValidator::class, $msgValidator,
			'returns a valid object of MessageValidator class.' );
	}
}
