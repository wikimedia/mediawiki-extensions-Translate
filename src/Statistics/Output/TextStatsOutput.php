<?php

namespace MediaWiki\Extension\Translate\Statistics\Output;

/** Output text. To be used on a terminal for example. */
class TextStatsOutput extends StatsOutput {
	/** @inheritDoc */
	public function element( $in, $heading = false ) {
		echo $in . "\t";
	}

	public function blockend() {
		echo "\n";
	}
}
