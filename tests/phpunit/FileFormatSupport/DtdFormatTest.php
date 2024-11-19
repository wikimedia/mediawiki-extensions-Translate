<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\FileFormatSupport;

use FileBasedMessageGroup;
use MediaWikiIntegrationTestCase;
use MessageGroupBase;

/**
 * The DtdFormat class is responsible for loading messages from .dtd
 * files.
 * These tests check that the message keys are loaded and saved correctly.
 * @author Niklas Laxström
 * @author Amir E. Aharoni
 * @license GPL-2.0-or-later
 */

/** @covers \MediaWiki\Extension\Translate\FileFormatSupport\DtdFormat */
class DtdFormatTest extends MediaWikiIntegrationTestCase {

	private const GROUP_CONFIGURATION = [
		'BASIC' => [
			'class' => FileBasedMessageGroup::class,
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'format' => 'Dtd',
		],
	];

	public function testParsing(): void {
		$file =
			<<<'DTD'
			<!--
			# Messages for Interlingua (interlingua)
			# Exported from translatewiki.net

			# Author: McDutchie
			-->
			<!ENTITY okawix.title "Okawix &okawix.vernum; - Navigator de Wikipedia">
			<!ENTITY okawix.back
			"Retro">
			DTD;

		/** @var FileBasedMessageGroup $group */
		$group = MessageGroupBase::factory( self::GROUP_CONFIGURATION );
		$dtdFormat = new DtdFormat( $group );
		$parsed = $dtdFormat->readFromVariable( $file );
		$expected = [
			'okawix.title' => 'Okawix &okawix.vernum; - Navigator de Wikipedia',
			'okawix.back' => 'Retro',
		];
		$expected = [ 'MESSAGES' => $expected, 'AUTHORS' => [ 'McDutchie' ] ];
		$this->assertEquals( $expected, $parsed );
	}
}
