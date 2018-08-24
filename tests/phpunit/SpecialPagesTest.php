<?php
/**
 * General integration test for special pages.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

/**
 * Integration tests for making sure special pages do not fail in unexpected ways when viewed
 * without parameters as anonymous or logged in user.
 * @group Database
 * @group large
 */
class SpecialPagesTest extends MediaWikiTestCase {
	protected function setUp() {
		parent::setUp();

		global $wgHooks;
		$this->setMwGlobals( [
			'wgHooks' => $wgHooks,
			'wgTranslateTranslationServices' => [],
			'wgTranslateCacheDirectory' => $this->getNewTempDirectory(),
		] );
		$wgHooks['TranslatePostInitGroups'] = [];

		$mg = MessageGroups::singleton();
		$mg->setCache( wfGetCache( 'hash' ) );
		$mg->recache();

		MessageIndex::setInstance( new HashMessageIndex() );
		MessageIndex::singleton()->rebuild();
	}

	public static function provideSpecialPages() {
		require __DIR__ . '/../../Autoload.php';
		global $wgSpecialPages;

		$pages = [];
		foreach ( $wgSpecialPages as $name => $class ) {
			if ( is_string( $class ) && isset( $al[$class] ) ) {
				$pages[] = [ $name ];
			}
		}

		return $pages;
	}

	/**
	 * @dataProvider provideSpecialPages
	 */
	public function testSpecialPage( $name ) {
		$page = TranslateUtils::getSpecialPage( $name );
		$title = $page->getPageTitle();

		$context = RequestContext::newExtraneousContext( $title );
		$page->setContext( $context );

		try {
			$page->run( null );
		} catch ( PermissionsError $e ) {
			// This is okay
			wfDebug( 'Permissions error caught; expected.' );
		} catch ( ErrorPageError $e ) {
			// This is okay as well
			wfDebug( 'Page error caught; expected.' );
		}

		$this->assertTrue( true, "Special page $name was executed successfully with anon user" );

		$user = new MockSuperUser();
		$context->setUser( $user );
		$page->setContext( $context );

		// This should not throw permission errors
		try {
			$page->run( null );
		} catch ( ErrorPageError $e ) {
			// This is okay here
			wfDebug( 'Page error caught; expected.' );
		}

		$this->assertTrue( true, "Special page $name was executed succesfully with super user" );
	}
}
