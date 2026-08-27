<?php

namespace MediaWiki\Extension\Translate\Statistics\Output;

/** csv output. Some people love excel */
class CsvStatsOutput extends StatsOutput {
	/** @inheritDoc */
	public function element( $in, $heading = false ) {
		echo $in . ";";
	}

	public function blockend() {
		echo "\n";
	}
}
