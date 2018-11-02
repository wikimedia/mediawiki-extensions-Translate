<?php

/**
 * Provides heading, summaryheading and free text addition for stats output in
 * wiki format.
 *
 * @ingroup Stats
 */
class TranslateStatsOutput extends WikiStatsOutput {
	public function heading() {
		echo '{| class="mw-ext-translate-groupstatistics sortable wikitable" border="2" ' .
			'cellpadding="4" cellspacing="0" style="background-color: #F9F9F9; border: ' .
			'1px #AAAAAA solid; border-collapse: collapse; clear:both;" width="100%"' . "\n";
	}

	public function summaryheading() {
		echo "\n" . '{| class="mw-ext-translate-groupstatistics sortable wikitable" ' .
			'border="2" cellpadding="4" cellspacing="0" style="background-color: #F9F9F9; ' .
			'border: 1px #AAAAAA solid; border-collapse: collapse; clear:both;"' . "\n";
	}

	public function addFreeText( $freeText ) {
		echo $freeText;
	}
}
