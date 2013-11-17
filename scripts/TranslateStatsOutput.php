<?php

/**
 * Provides heading, summaryheading and free text addition for stats output in
 * wiki format.
 *
 * @ingroup Stats
 */
class TranslateStatsOutput extends WikiStatsOutput {
	function heading() {
		echo '{| class="mw-ext-translate-groupstatistics sortable wikitable" border="2" ' .
			'cellpadding="4" cellspacing="0" style="background-color: #F9F9F9; border: ' .
			'1px #AAAAAA solid; border-collapse: collapse; clear:both;" width="100%"' . "\n";
	}

	function summaryheading() {
		echo "\n" . '{| class="mw-ext-translate-groupstatistics sortable wikitable" ' .
			'border="2" cellpadding="4" cellspacing="0" style="background-color: #F9F9F9; ' .
			'border: 1px #AAAAAA solid; border-collapse: collapse; clear:both;"' . "\n";
	}

	function addFreeText( $freeText ) {
		echo $freeText;
	}
}
