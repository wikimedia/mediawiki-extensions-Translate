<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\Statistics;

use MediaWiki\Tests\Api\ApiTestCase;
use MessageGroupTestTrait;
use MockWikiMessageGroup;

/**
 * @license GPL-2.0-or-later
 * @group medium
 * @group Database
 * @covers \MediaWiki\Extension\Translate\Statistics\QueryLanguageStatsActionApi
 */
class QueryLanguageStatsActionApiTest extends ApiTestCase {
	use MessageGroupTestTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->setupGroupTestEnvironmentWithGroups( $this, $this->getTestGroups() );
	}

	public function getTestGroups(): array {
		$list['full-group'] = new MockWikiMessageGroup( 'full-group', [
			'msg_1' => 'definition one',
			'msg_2' => 'definition two',
		] );
		$list['empty-group'] = new MockWikiMessageGroup( 'empty-group', [
			'msg_3' => 'definition three',
			'msg_4' => 'definition four',
		] );
		$list['partial-group'] = new MockWikiMessageGroup( 'partial-group', [
			'msg_5' => 'definition five',
			'msg_6' => 'definition six',
		] );

		return $list;
	}

	protected function seedTranslations(): void {
		$user = $this->getTestSysop()->getUser();
		// Fully translate full-group
		$this->editPage( 'MediaWiki:Msg_1/fi', 'käännös yksi', '', NS_MAIN, $user );
		$this->editPage( 'MediaWiki:Msg_2/fi', 'käännös kaksi', '', NS_MAIN, $user );
		// Partially translate partial-group
		$this->editPage( 'MediaWiki:Msg_5/fi', 'käännös viisi', '', NS_MAIN, $user );
		// empty-group has no translations, but stats must be cached for all groups
		// so the API execute() loop does not break on a cache miss before reaching it.
		MessageGroupStats::forLanguage( 'fi' );
	}

	public function testBasicRequest(): void {
		[ $data ] = $this->doApiRequest( [
			'action' => 'query',
			'meta' => 'languagestats',
			'lslanguage' => 'fi',
			'continue' => '',
		] );

		$this->assertArrayHasKey( 'query', $data );
		$this->assertArrayHasKey( 'languagestats', $data['query'] );
	}

	public function testSuppressComplete(): void {
		$this->seedTranslations();

		[ $data ] = $this->doApiRequest( [
			'action' => 'query',
			'meta' => 'languagestats',
			'lslanguage' => 'fi',
			'lssuppresscomplete' => '1',
			'continue' => '',
		] );

		$groups = array_column( $data['query']['languagestats'], 'group' );
		$this->assertNotContains( 'full-group', $groups,
			'Fully translated group is suppressed' );
		$this->assertContains( 'partial-group', $groups,
			'Partially translated group is not suppressed' );
		$this->assertContains( 'empty-group', $groups,
			'Empty group is not suppressed by suppresscomplete' );
	}

	public function testSuppressEmpty(): void {
		$this->seedTranslations();

		[ $data ] = $this->doApiRequest( [
			'action' => 'query',
			'meta' => 'languagestats',
			'lslanguage' => 'fi',
			'lssuppressempty' => '1',
			'continue' => '',
		] );

		$groups = array_column( $data['query']['languagestats'], 'group' );
		$this->assertNotContains( 'empty-group', $groups,
			'Empty group is suppressed' );
		$this->assertContains( 'full-group', $groups,
			'Fully translated group is not suppressed by suppressempty' );
		$this->assertContains( 'partial-group', $groups,
			'Partially translated group is not suppressed by suppressempty' );
	}

	public function testSuppressBoth(): void {
		$this->seedTranslations();

		[ $data ] = $this->doApiRequest( [
			'action' => 'query',
			'meta' => 'languagestats',
			'lslanguage' => 'fi',
			'lssuppresscomplete' => '1',
			'lssuppressempty' => '1',
			'continue' => '',
		] );

		$groups = array_column( $data['query']['languagestats'], 'group' );
		$this->assertNotContains( 'full-group', $groups,
			'Fully translated group is suppressed' );
		$this->assertNotContains( 'empty-group', $groups,
			'Empty group is suppressed' );
		$this->assertContains( 'partial-group', $groups,
			'Partially translated group is not suppressed' );
	}
}
