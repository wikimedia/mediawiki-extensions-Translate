<?php
/**
 * Test for the utilities for the sandbox feature of Translate.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 */

use MediaWiki\MediaWikiServices;

/** @group Database */
class TranslateSandboxTest extends MediaWikiIntegrationTestCase {
	protected function setUp(): void {
		parent::setUp();
		$this->setMwGlobals( [
			'wgTranslateUseSandbox' => true,
			'wgTranslateSandboxPromotedGroup' => 'translator',
		] );

		// Make sure the hooks are installed even if $wgTranslateUseSandbox is false.
		TranslateHooks::setupTranslate();
		$this->tablesUsed[] = 'user';
	}

	/**
	 * @param User $user
	 * @return array|string[]
	 */
	private function getUserGroups( User $user ): array {
		$userGroupManager = MediaWikiServices::getInstance()->getUserGroupManager();
		$groups = $userGroupManager->getUserGroups( $user );

		return $groups;
	}

	public function testAddUser() {
		$user = TranslateSandbox::addUser( 'Test user', 'test@blackhole.io', 'test password' );
		$this->assertTrue( $user->isRegistered(), 'User exists' );

		$groups = array_unique( $this->getUserGroups( $user ) );

		$this->assertSame( [ 'translate-sandboxed' ], $groups, 'User is in the sandboxed group' );
	}

	public function testDeleteUser() {
		$user = TranslateSandbox::addUser( 'Test user2', 'test@blackhole.io', 'test password' );
		TranslateSandbox::deleteUser( $user );
		$this->assertFalse( $user->isRegistered(), 'User no longer exists' );
	}

	public function testDeleteUserPromoted() {
		$user = TranslateSandbox::addUser( 'Test user3', 'test@blackhole.io', 'test password' );
		TranslateSandbox::promoteUser( $user );
		$this->expectException( MWException::class );
		$this->expectExceptionMessage( 'Not a sandboxed user' );
		TranslateSandbox::deleteUser( $user );
	}

	public function testGetUsers() {
		$atStart = TranslateSandbox::getUsers()->count();

		$user = TranslateSandbox::addUser( 'Test user4', 'test@blackhole.io', 'test password' );

		$this->assertEquals(
			$atStart + 1, TranslateSandbox::getUsers()->count(),
			'One sandboxed user created'
		);

		TranslateSandbox::deleteUser( $user );
		$this->assertEquals(
			$atStart, TranslateSandbox::getUsers()->count(),
			'No sandboxed users after deleted'
		);
	}

	public function testGetUsersPromotion() {
		$atStart = TranslateSandbox::getUsers()->count();

		$user = TranslateSandbox::addUser( 'Test user5', 'test@blackhole.io', 'test password' );
		$this->assertEquals(
			$atStart + 1,
			TranslateSandbox::getUsers()->count(),
			'One sandboxed user created'
		);

		TranslateSandbox::promoteUser( $user );
		$this->assertEquals(
			$atStart,
			TranslateSandbox::getUsers()->count(),
			'No sandboxed users after promotion'
		);
	}

	public function testPromoteUser() {
		$user = TranslateSandbox::addUser( 'Test user6', 'test@blackhole.io', 'test password' );
		TranslateSandbox::promoteUser( $user );

		$this->assertContains( 'translator', $this->getUserGroups( $user ) );
	}

	public function testPermissions() {
		$user = TranslateSandbox::addUser( 'Test user7', 'test@blackhole.io', 'test password' );
		$title = Title::makeTitle( NS_USER_TALK, $user->getName() );
		$pm = MediaWikiServices::getInstance()->getPermissionManager();

		$this->assertFalse(
			$pm->userCan( 'edit', $user, $title ),
			'Sandboxed users cannot edit their own talk page'
		);
		TranslateSandbox::promoteUser( $user );
		$this->assertTrue(
			$pm->userCan( 'edit', $user, $title ),
			'Promoted users can edit their own talk page'
		);
	}

	public function testIsSandboxed() {
		$userNotInGroup = $this->getTestUser()->getUser();
		$userInGroup = $this->getTestUser( [ 'translate-sandboxed' ] )->getUser();

		$this->assertTrue( TranslateSandbox::isSandboxed( $userInGroup ) );
		$this->assertFalse( TranslateSandbox::isSandboxed( $userNotInGroup ) );
	}
}
