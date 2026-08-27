<?php

namespace MediaWiki\Extension\Translate\Statistics\Output;

use MediaWiki\MediaWikiServices;
use SpecialVersion;

/** Outputs WikiText */
class WikiStatsOutput extends StatsOutput {
	public function heading() {
		global $wgDummyLanguageCodes;
		$version = SpecialVersion::getVersion( 'nodb' );
		// @phan-suppress-next-line SecurityCheck-XSS The code is to be interpreted as wikitext
		echo "'''Statistics are based on:''' <code>" . $version . "</code>\n\n";
		echo 'English (en) is excluded because it is the default localization';
		if ( is_array( $wgDummyLanguageCodes ) ) {
			$dummyCodes = [];
			foreach ( $wgDummyLanguageCodes as $dummyCode => $correctCode ) {
				$dummyCodes[] =
					MediaWikiServices::getInstance()->getLanguageNameUtils()->getLanguageName( $dummyCode ) . ' (' .
					$dummyCode . ')';
			}
			echo ', as well as the following languages that are not intended for ' .
				'system message translations, usually because they redirect to other ' . 'language codes: ' .
				implode( ', ', $dummyCodes );
		}
		# dot to end sentence
		echo ".\n\n";
		echo '{| class="sortable wikitable" border="2" style="background-color: #F9F9F9; ' .
			'border: 1px #AAAAAA solid; border-collapse: collapse; clear:both; width:100%;"' . "\n";
	}

	public function footer() {
		echo "|}\n";
	}

	public function blockstart() {
		echo "|-\n";
	}

	public function blockend() {
		echo '';
	}

	/** @inheritDoc */
	public function element( $in, $heading = false ) {
		echo ( $heading ? '!' : '|' ) . "$in\n";
	}

	/** @inheritDoc */
	public function formatPercent( $subset, $total, $revert = false, $accuracy = 2 ) {
		// phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged
		$v = @round( 255 * $subset / $total );

		if ( $revert ) {
			# Weigh reverse with factor 20 so coloring takes effect more quickly as
			# this option is used solely for reporting 'bad' percentages.
			$v *= 20;
			if ( $v > 255 ) {
				$v = 255;
			}
			$v = 255 - $v;
		}
		if ( $v < 128 ) {
			# Red to Yellow
			$red = 'FF';
			$green = sprintf( '%02X', 2 * $v );
		} else {
			# Yellow to Green
			$red = sprintf( '%02X', 2 * ( 255 - $v ) );
			$green = 'FF';
		}
		$blue = '00';
		$color = $red . $green . $blue;

		$percent = parent::formatPercent( $subset, $total, $revert, $accuracy );

		return 'style="background-color:#' . $color . ';"|' . $percent;
	}
}
