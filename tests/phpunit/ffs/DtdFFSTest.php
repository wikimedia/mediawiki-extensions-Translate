<?php
/**
 * The DtdFFS class is responsible for loading messages from .dtd
 * files.
 * These tests check that the message keys are loaded and saved correctly.
 * @author Niklas Laxström
 * @author Amir E. Aharoni
 * @file
 * @license GPL-2.0-or-later
 */

class DtdFFSTest extends MediaWikiTestCase {

	protected $groupConfiguration = [
		'BASIC' => [
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		],
		'FILES' => [
			'class' => 'DtdFFS',
		],
	];

	public function testParsing() {
		$file =
			<<<DTD
			<!--
# Messages for Interlingua (interlingua)
# Exported from translatewiki.net

# Author: McDutchie
-->
<!ENTITY okawix.title "Okawix &okawix.vernum; - Navigator de Wikipedia">
<!ENTITY okawix.back
"Retro">
DTD;

		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new DtdFFS( $group );
		$parsed = $ffs->readFromVariable( $file );
		$expected = [
			'okawix.title' => 'Okawix &okawix.vernum; - Navigator de Wikipedia',
			'okawix.back' => 'Retro',
		];
		$expected = [ 'MESSAGES' => $expected, 'AUTHORS' => [ 'McDutchie' ] ];
		$this->assertEquals( $expected, $parsed );
	}
}
