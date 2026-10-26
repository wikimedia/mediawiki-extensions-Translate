<?php
declare( strict_types = 1 );

use MediaWiki\Extension\Translate\Diagnostics\FuzzyPluralTranslationsMaintenanceScript;
use MediaWiki\Extension\Translate\MessageGroupProcessing\RevTagStore;
use MediaWiki\Extension\Translate\MessageGroups\AggregateMessageGroup;
use MediaWiki\Extension\Translate\MessageGroups\MessageGroupBase;
use MediaWiki\Extension\Translate\MessageGroups\WikiPageMessageGroup;
use MediaWiki\Extension\Translate\MessageLoading\MessageHandle;
use MediaWiki\Extension\Translate\SystemUsers\FuzzyBot;
use MediaWiki\Maintenance\MaintenanceFatalError;
use MediaWiki\Revision\RenderedRevision;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MediaWiki\Tests\Maintenance\MaintenanceBaseTestCase;
use MediaWiki\Title\Title;

/**
 * @license GPL-2.0-or-later
 * @group Database
 * @group medium
 * @covers \MediaWiki\Extension\Translate\Diagnostics\FuzzyPluralTranslationsMaintenanceScript
 */
class FuzzyPluralTranslationsMaintenanceScriptTest extends MaintenanceBaseTestCase {
	use MessageGroupTestTrait;

	private const SUMMARY = 'Plural forms need review. See [[Portal:Fi#plural]]';

	private const TRANSLATIONS = [
		'Noplural' => 'Hei',
		'Oneform' => '{{PLURAL:$1|kohde}}',
		'Manyforms' => '$1 {{PLURAL:$1|kohde|kohdetta}}',
		'Explicit' => '{{PLURAL:$1|0=ei mitään|kohde|kohdetta}}',
		'Lowercase' => '{{plural:$1|kohde|kohdetta}}',
		'Gettext' => '{{PLURAL:GETTEXT|kohde|kohdetta}}',
		'Alreadyfuzzy' => '!!FUZZY!!{{PLURAL:$1|vanha|vanhaa}}',
		'Markeronly' => '!!FUZZY!!{{PLURAL:$1|merkki|merkkiä}}',
		'Shared' => '{{PLURAL:$1|jaettu|jaettua}}',
	];

	protected function getMaintenanceClass() {
		return FuzzyPluralTranslationsMaintenanceScript::class;
	}

	protected function setUp(): void {
		parent::setUp();

		$messages = array_fill_keys( array_map( 'strtolower', array_keys( self::TRANSLATIONS ) ), 'Source' );
		$messages['untranslated'] = '{{PLURAL:$1|item|items}}';
		// Only meta groups can share messages with other groups
		$metaGroup = new class( 'plural-b', [ 'shared' => 'Source', 'other' => 'Source' ] )
			extends MockWikiMessageGroup {
			/** @var bool */
			protected $meta = true;
		};
		$groups = [
			'plural-a' => new MockWikiMessageGroup( 'plural-a', $messages ),
			'plural-b' => $metaGroup,
			'page-Plural page' => new class( 'page-Plural page', 'Plural page', 'en' ) extends WikiPageMessageGroup {
				/** @inheritDoc */
				public function getDefinitions() {
					return [ 'Plural_page/1' => 'Source' ];
				}
			},
			'plural-agg' => MessageGroupBase::factory( [
				'BASIC' => [
					'class' => AggregateMessageGroup::class,
					'id' => 'plural-agg',
					'namespace' => NS_MEDIAWIKI,
					'meta' => true,
				],
				'GROUPS' => [ 'plural-a', 'plural-b' ],
			] ),
		];
		$this->setupGroupTestEnvironmentWithGroups( $this, $groups );

		$user = $this->getTestUser()->getUser();
		foreach ( self::TRANSLATIONS as $key => $text ) {
			$this->editPage( Title::makeTitle( NS_MEDIAWIKI, "$key/fi" ), $text, '', NS_MAIN, $user );
		}

		// A translation with the fuzzy marker in its text, but without the fuzzy tag
		$this->getDb()->newDeleteQueryBuilder()
			->deleteFrom( 'revtag' )
			->where( [
				'rt_page' => $this->getTitle( 'Markeronly' )->getArticleID(),
				'rt_type' => RevTagStore::FUZZY_TAG,
			] )
			->caller( __METHOD__ )
			->execute();
	}

	private function getTitle( string $key ): Title {
		return Title::makeTitle( NS_MEDIAWIKI, "$key/fi" );
	}

	private function getLatestRevision( string $key ): RevisionRecord {
		return $this->getServiceContainer()->getRevisionLookup()->getRevisionByTitle( $this->getTitle( $key ) );
	}

	/** @return int[] Latest revision IDs indexed by message key */
	private function getLatestRevisionIds(): array {
		$ids = [];
		foreach ( array_keys( self::TRANSLATIONS ) as $key ) {
			$ids[$key] = $this->getLatestRevision( $key )->getId();
		}
		return $ids;
	}

	private function runScript( array $options ): string {
		[ $output, $error ] = $this->runScriptAndCatchFatalError( $options );
		if ( $error ) {
			throw $error;
		}
		return $output;
	}

	/** @return array Output of the script, and the fatal error if it failed */
	private function runScriptAndCatchFatalError( array $options ): array {
		$this->maintenance = $this->createMaintenance();
		$options += [ 'language' => 'fi', 'group' => 'plural-agg', 'summary' => self::SUMMARY ];
		foreach ( $options as $name => $value ) {
			$this->maintenance->setOption( $name, $value );
		}

		$error = null;
		ob_start();
		try {
			$this->maintenance->execute();
		} catch ( MaintenanceFatalError $e ) {
			$error = $e;
		} finally {
			$output = ob_get_clean();
		}
		return [ $output, $error ];
	}

	public function testDryRun() {
		$before = $this->getLatestRevisionIds();
		$this->assertFalse( ( new MessageHandle( $this->getTitle( 'Markeronly' ) ) )->isFuzzy() );

		$output = $this->runScript( [] );

		$this->assertSame( $before, $this->getLatestRevisionIds(), 'Dry run must not create revisions' );
		$expectedList = implode( "\n", [
			'MediaWiki:Alreadyfuzzy/fi [already fuzzy]',
			'MediaWiki:Explicit/fi',
			'MediaWiki:Lowercase/fi',
			'MediaWiki:Manyforms/fi',
			'MediaWiki:Markeronly/fi',
			'MediaWiki:Oneform/fi',
			'MediaWiki:Shared/fi',
		] );
		$this->assertStringContainsString( "Edit summary: " . self::SUMMARY . "\n$expectedList\n", $output );
		$this->assertStringNotContainsString( 'Noplural', $output );
		$this->assertStringNotContainsString( 'Gettext', $output );
		$this->assertStringContainsString(
			'Group plural-a: 9 translations inspected, 7 with PLURAL, 1 of those already fuzzy, 0 already processed',
			$output
		);
		$this->assertStringContainsString(
			'Group plural-b: 0 translations inspected, 0 with PLURAL',
			$output,
			'Messages shared between subgroups are only processed once'
		);
		$this->assertStringContainsString( 'Dry run: no changes were made.', $output );
	}

	public function testReally() {
		$before = $this->getLatestRevisionIds();
		$fuzzyBot = FuzzyBot::getUser();

		$output = $this->runScript( [ 'really' => true ] );

		$this->assertStringNotContainsString( 'FAILED', $output );
		$marked = [ 'Oneform', 'Manyforms', 'Explicit', 'Lowercase', 'Shared', 'Alreadyfuzzy', 'Markeronly' ];
		foreach ( $marked as $key ) {
			$revision = $this->getLatestRevision( $key );
			$this->assertNotSame( $before[$key], $revision->getId(), "$key has a new revision" );
			$this->assertTrue( $revision->getUser()->equals( $fuzzyBot ), "$key was edited by FuzzyBot" );
			$this->assertSame( self::SUMMARY, $revision->getComment()->text, "$key has the summary" );
			$this->assertTrue( ( new MessageHandle( $this->getTitle( $key ) ) )->isFuzzy(), "$key is fuzzy" );

			$text = $revision->getContent( SlotRecord::MAIN )->getText();
			if ( $key === 'Alreadyfuzzy' ) {
				$this->assertSame( self::TRANSLATIONS[$key], $text, 'Already fuzzy text is not changed' );
			} elseif ( $key === 'Markeronly' ) {
				// Marker without the fuzzy tag is doubled, like FuzzyTranslationsMaintenanceScript does
				$this->assertSame( TRANSLATE_FUZZY . self::TRANSLATIONS[$key], $text );
			} else {
				$this->assertSame( TRANSLATE_FUZZY . self::TRANSLATIONS[$key], $text, "$key has the fuzzy marker" );
			}
		}

		foreach ( [ 'Noplural', 'Gettext' ] as $key ) {
			$this->assertSame( $before[$key], $this->getLatestRevision( $key )->getId(), "$key is not edited" );
			$this->assertFalse( ( new MessageHandle( $this->getTitle( $key ) ) )->isFuzzy(), "$key is not fuzzy" );
		}

		// Running again with the same summary does not edit again
		$afterFirstRun = $this->getLatestRevisionIds();
		$output = $this->runScript( [ 'really' => true ] );
		$this->assertSame( $afterFirstRun, $this->getLatestRevisionIds(), 'Second run must not create revisions' );
		$this->assertSame( 7, substr_count( $output, 'already processed]... SKIPPED' ) );
	}

	public function testFailedEditDoesNotStopTheRun() {
		$this->setTemporaryHook( 'MultiContentSave', static function ( RenderedRevision $renderedRevision ) {
			if ( $renderedRevision->getRevision()->getPage()->getDBkey() === 'Oneform/fi' ) {
				throw new TypeError( 'Simulated failure' );
			}
		} );
		$before = $this->getLatestRevisionIds();

		[ $output, $error ] = $this->runScriptAndCatchFatalError( [ 'really' => true ] );

		$this->assertNotNull( $error, 'The script fails at the end' );
		$this->assertSame( '1 edit(s) failed.', $error->getMessage() );
		$this->assertStringContainsString( "MediaWiki:Oneform/fi... FAILED: Simulated failure\n", $output );
		$this->assertSame( $before['Oneform'], $this->getLatestRevision( 'Oneform' )->getId() );
		$this->assertNotSame(
			$before['Shared'],
			$this->getLatestRevision( 'Shared' )->getId(),
			'Translations after the failed one are still processed'
		);
	}

	public function testRenderJobsAreQueuedAlsoForSkippedTranslations() {
		$user = $this->getTestUser()->getUser();
		$this->editPage( Title::makeTitle( NS_MAIN, 'Plural page/fi' ), 'Rendered', '', NS_MAIN, $user );
		$this->editPage(
			Title::makeTitle( NS_TRANSLATIONS, 'Plural page/1/fi' ), '{{PLURAL:$1|sivu|sivua}}', '', NS_MAIN, $user
		);
		$queue = $this->getServiceContainer()->getJobQueueGroup()->get( 'RenderTranslationPageJob' );
		$queue->delete();

		$output = $this->runScript( [ 'group' => 'page-Plural page', 'really' => true ] );
		$this->assertStringContainsString( "Translations:Plural page/1/fi... OK\n", $output );
		$this->assertSame( 1, $queue->getSize() );

		// An earlier run that stopped before queueing the render jobs
		$queue->delete();
		$output = $this->runScript( [ 'group' => 'page-Plural page', 'really' => true ] );
		$this->assertStringContainsString( 'already processed]... SKIPPED', $output );
		$this->assertSame( 1, $queue->getSize() );
		$this->assertSame( 'Plural page/fi', $queue->pop()->getTitle()->getPrefixedText() );
	}

	public function testSourceLanguageIsSkipped() {
		$output = $this->runScript( [ 'language' => 'en' ] );

		$this->assertStringContainsString( 'Skipping group plural-a: source language of the group', $output );
		$this->assertStringContainsString( 'Skipping group plural-b: source language of the group', $output );
		$this->assertStringContainsString( 'Total: 0 translations inspected', $output );
	}

	public static function provideInvalidOptions() {
		yield 'unknown group' => [ [ 'group' => 'no-such-group' ] ];
		yield 'invalid language' => [ [ 'language' => 'not a language' ] ];
		yield 'message documentation' => [ [ 'language' => 'qqq' ] ];
		yield 'empty summary' => [ [ 'summary' => ' ' ] ];
		yield 'too long summary' => [ [ 'summary' => str_repeat( 'a', 501 ) ] ];
	}

	/** @dataProvider provideInvalidOptions */
	public function testInvalidOptions( array $options ) {
		$before = $this->getLatestRevisionIds();
		$this->expectCallToFatalError();
		try {
			$this->runScript( $options + [ 'really' => true ] );
		} finally {
			$this->assertSame( $before, $this->getLatestRevisionIds() );
		}
	}
}
