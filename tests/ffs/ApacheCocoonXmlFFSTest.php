<?php
/**
 * Tests for ApacheCocoonXmlFFS
 *
 * @file
 * @author Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

class ApacheCocoonXmlFFSTest extends AndroidXmlFFSTest {
	protected $groupConfiguration = array(
		'BASIC' => array(
			'class' => 'FileBasedMessageGroup',
			'id' => 'test-id',
			'label' => 'Test Label',
			'namespace' => 'NS_MEDIAWIKI',
			'description' => 'Test description',
		),
		'FILES' => array(
			'class' => 'ApacheCocoonXmlFFS',
			'sourcePattern' => '',
		),
	);

	public function testParsing() {
		$file =
<<<XML
<?xml version="1.0" encoding="utf-8"?>
<catalogue xml:lang="fi" xmlns:18n="http://apache.org/cocoon/i18n/2.1">
	<message key="wpt_voicerec">Voice recording</message>
	<message key="wpt_stillimage" fuzzy="true">Picture</message>
</resources>
XML;

		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new ApacheCocoonXmlFFS( $group );
		$parsed = $ffs->readFromVariable( $file );
		$expected = array(
			'wpt_voicerec' => 'Voice recording',
			'wpt_stillimage' => '!!FUZZY!!Picture',
		);
		$expected = array( 'MESSAGES' => $expected, 'AUTHORS' => array() );
		$this->assertEquals( $expected, $parsed );
	}

	public function testWrite() {
		/**
		 * @var FileBasedMessageGroup $group
		 */
		$group = MessageGroupBase::factory( $this->groupConfiguration );
		$ffs = new AndroidXmlFFS( $group );

		$messages = array(
			'ko=26ra' => 'wawe',
			'foobar' => '!!FUZZY!!Kissa kala <koira> "a\'b',
		);
		$collection = new MockMessageCollection( $messages );

		$xml = $ffs->writeIntoVariable( $collection );
		$parsed = $ffs->readFromVariable( $xml );
		$expected = array( 'MESSAGES' => $messages, 'AUTHORS' => array() );
		$this->assertEquals( $expected, $parsed );
	}
}
