<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorSandbox;

use MediaWiki\Extension\Translate\HookHandler;
use MediaWiki\Extension\Translate\Services;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWikiIntegrationTestCase;

/**
 * Test for the utilities for the sandbox feature of Translate.
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @group Database
 * @covers \MediaWiki\Extension\Translate\TranslatorSandbox\TranslateSandbox
 */
class TranslateSandboxTest extends MediaWikiIntegrationTestCase {
	private TranslateSandbox $translateSandbox;

	protected function setUp(): void {
		parent::setUp();
		$this->overrideConfigValues( [
			'TranslateUseSandbox' => true,
			'TranslateSandboxPromotedGroup' => 'translator',
		] );
		$this->translateSandbox = Services::getInstance()->getTranslateSandbox();
		// Make sure the hooks are installed even if $wgTranslateUseSandbox is false.
		HookHandler::setupTranslate();
	}

	private function assertLogTypesAreUnique() {
		global $wgLogTypes;
		$seen = [];
		foreach ( $wgLogTypes as $logType ) {
			$this->assertNotContains( $logType, $seen, 'Got types: ' . print_r( $wgLogTypes, true ) );
			$seen[] = $logType;
		}
	}

	protected function assertPostConditions(): void {
		parent::assertPostConditions();
		$this->assertLogTypesAreUnique();
	}

	/**
	 * @param User $user
	 * @return array|string[]
	 */
	private function getUserGroups( User $user ): array {
		$userGroupManager = $this->getServiceContainer()->getUserGroupManager();
		$groups = $userGroupManager->getUserGroups( $user );

		return $groups;
	}

	public function testAddUser(): void {
		$user = $this->translateSandbox->addUser( 'Test user', 'test@blackhole.io', 'test password' );
		$this->assertTrue( $user->isRegistered(), 'User exists' );

		$groups = array_unique( $this->getUserGroups( $user ) );

		$this->assertSame( [ 'translate-sandboxed' ], $groups, 'User is in the sandboxed group' );
	}

	public function testDeleteUser(): void {
		$user = $this->translateSandbox->addUser( 'Test user2', 'test@blackhole.io', 'test password' );
		$this->translateSandbox->deleteUser( $user );
		$this->assertFalse( $user->isRegistered(), 'User no longer exists' );
	}

	public function testDeleteUserPromoted(): void {
		$user = $this->translateSandbox->addUser( 'Test user3', 'test@blackhole.io', 'test password' );
		$this->translateSandbox->promoteUser( $user );
		$this->expectException( UserNotSandboxedException::class );
		$this->translateSandbox->deleteUser( $user );
	}

	public function testGetUsers(): void {
		$atStart = $this->translateSandbox->getUsers()->count();

		$user = $this->translateSandbox->addUser( 'Test user4', 'test@blackhole.io', 'test password' );

		$this->assertEquals(
			$atStart + 1, $this->translateSandbox->getUsers()->count(),
			'One sandboxed user created'
		);

		$this->translateSandbox->deleteUser( $user );
		$this->assertEquals(
			$atStart, $this->translateSandbox->getUsers()->count(),
			'No sandboxed users after deleted'
		);
	}

	public function testGetUsersPromotion(): void {
		$atStart = $this->translateSandbox->getUsers()->count();

		$user = $this->translateSandbox->addUser( 'Test user5', 'test@blackhole.io', 'test password' );
		$this->assertEquals(
			$atStart + 1,
			$this->translateSandbox->getUsers()->count(),
			'One sandboxed user created'
		);

		$this->translateSandbox->promoteUser( $user );
		$this->assertEquals(
			$atStart,
			$this->translateSandbox->getUsers()->count(),
			'No sandboxed users after promotion'
		);
	}

	public function testPromoteUser(): void {
		$user = $this->translateSandbox->addUser( 'Test user6', 'test@blackhole.io', 'test password' );
		$this->translateSandbox->promoteUser( $user );

		$this->assertContains( 'translator', $this->getUserGroups( $user ) );
	}

	public function testPermissions(): void {
		$user = $this->translateSandbox->addUser( 'Test user7', 'test@blackhole.io', 'test password' );
		$title = Title::makeTitle( NS_USER_TALK, $user->getName() );
		$pm = $this->getServiceContainer()->getPermissionManager();

		$this->assertFalse(
			$pm->userCan( 'edit', $user, $title ),
			'Sandboxed users cannot edit their own talk page'
		);
		$this->translateSandbox->promoteUser( $user );
		$this->assertTrue(
			$pm->userCan( 'edit', $user, $title ),
			'Promoted users can edit their own talk page'
		);
	}

	public function testIsSandboxed(): void {
		$userNotInGroup = $this->getTestUser()->getUser();
		$userInGroup = $this->getTestUser( [ 'translate-sandboxed' ] )->getUser();

		$this->assertTrue( $this->translateSandbox->isSandboxed( $userInGroup ) );
		$this->assertFalse( $this->translateSandbox->isSandboxed( $userNotInGroup ) );
	}
}
