<?php

namespace MediaWiki\Extension\Translate\Synchronization;

use MediaWikiUnitTestCase;
use MessageUpdateJob;
use Title;

/** @covers \MediaWiki\Extension\Translate\Synchronization\MessageUpdateParameter */
class MessageUpdateParameterTest extends MediaWikiUnitTestCase {
	/** @dataProvider provideSerializable */
	public function testSerializable(
		string $title,
		string $content,
		bool $isRename,
		string $target = '',
		string $replacement = '',
		bool $isFuzzy = false,
		array $otherLangs = []
	) {
		$job = $this->getJobFromInput(
			$title, $content, $isRename, $target, $replacement, $isFuzzy, $otherLangs
		);

		$messageParam = MessageUpdateParameter::createFromJob( $job );

		$serializedMessageParam = unserialize( serialize( $messageParam ) );
		$this->assertEquals( $messageParam, $serializedMessageParam );
	}

	/** @dataProvider provideSerializable */
	public function testCreateFromJob(
		string $title,
		string $content,
		bool $isRename,
		string $target = '',
		string $replacement = '',
		bool $isFuzzy = false,
		array $otherLangs = []
	) {
		$job = $this->getJobFromInput(
			$title, $content, $isRename, $target, $replacement, $isFuzzy, $otherLangs
		);
		$messageParams = MessageUpdateParameter::createFromJob( $job );
		$this->assertEquals( $title, $messageParams->getPageName() );
		$this->assertEquals( $content, $messageParams->getContent() );
		$this->assertEquals( $isRename, $messageParams->isRename() );
		$this->assertEquals( $isFuzzy, $messageParams->isFuzzy() );

		if ( $isRename ) {
			$this->assertEquals( $target, $messageParams->getTargetValue() );
			$this->assertEquals( $replacement, $messageParams->getReplacementValue() );
			$this->assertEquals( $otherLangs, $messageParams->getOtherLangs() );
		} else {
			$this->assertNull( $messageParams->getOtherLangs() );
		}
	}

	private function getJobFromInput(
		string $title,
		string $content,
		bool $isRename,
		string $target,
		string $replacement,
		bool $isFuzzy,
		array $otherLangs
	): MessageUpdateJob {
		$title = Title::makeTitle( NS_MAIN, $title );
		if ( $isRename ) {
			$job = MessageUpdateJob::newRenameJob(
				$title, $target, $replacement, $isFuzzy, $content, $otherLangs
			);
		} else {
			$job = MessageUpdateJob::newJob( $title, $content, $isFuzzy );
		}

		return $job;
	}

	public function provideSerializable() {
		yield [
			'Normal_Job/en',
			'Hello World!',
			false,
		];

		yield [
			'Rename_Job/en',
			'Hello World - Rename!',
			true,
			'target',
			'replacement',
			true,
			[ 'hello' => 'world' ]
		];
	}

}
