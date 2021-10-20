<?php
/**
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @file
 */

use MediaWiki\Extension\Translate\Validation\ValidationRunner;

/** @group medium */
class WikiPageMessageGroupTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->setTemporaryHook(
			'TranslatePostInitGroups',
			static function ( &$list ) {
				$anotherPageMessageGroup = new WikiPageMessageGroup( 'anotherpageid', 'mypage' );
				$anotherPageMessageGroup->setLabel( 'thelabel' ); // Example
				$anotherPageMessageGroup->setNamespace( 5 ); // Example

				$list['anotherpageid'] = $anotherPageMessageGroup;

				return false;
			}
		);

		$this->setTemporaryHook( 'TranslateInitGroupLoaders', [] );

		$mg = MessageGroups::singleton();
		$mg->setCache( new WANObjectCache( [ 'cache' => new HashBagOStuff() ] ) );
		$mg->recache();
	}

	public function testMessageValidator() {
		$group = MessageGroups::getGroup( 'anotherpageid' );
		$msgValidator = $group->getValidator();

		$this->assertInstanceOf( ValidationRunner::class, $msgValidator,
			'returns a valid object of ValidationRunner class.' );
	}
}
