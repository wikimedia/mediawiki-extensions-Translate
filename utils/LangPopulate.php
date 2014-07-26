<?php
/**
 * Class to populate the language bar
 *
 * @author Kunal Grover
 * @license Public Domain
 * @file
 */

class LangPopulate {
	static function langPopulateOrder ( Parser $parser, TranslatablePage $page ) {
		$status = $page->getTranslationPercentages();

		// Current language comes first
		$sourceLanguageCode = $page->getSourceLanguageCode();
		$outputLanguages = array( $sourceLanguageCode => $status[$sourceLanguageCode] );

		// Load all priority languages
		$priorityLangs = TranslateMetadata::get( $page->getMessageGroupId(), 'prioritylangs' );
		$filter = array();
		if ( strlen( $priorityLangs ) > 0 ) {
			$filter = array_flip( explode( ',', $priorityLangs ) );
			// Get the percentages for all filter languages if available
			$filter = array_intersect( $filter, $status );

			// If translation not started, we want priority languages to still be displayed with
			// 0% complete
			foreach ( $filter as $code => $percentage ) {
				if ( !isset( $percentage ) ) {
					$output[$percentage] = 0;
				}
			}
		}

		// If forced to use only priority languages, output languages are simply
		// source and priority languages
		$priorityForce = TranslateMetadata::get( $page->getMessageGroupId(), 'priorityforce' );
		if ( $priorityForce === 'on' ) {
			// Sort priority languages based on percentage completion
			arsort( $filter );
			$outputLanguages = array_merge( $outputLanguages, $filter );
		} else {
			// Now sort the languages by percentage of translation complete
			$otherLanguages = array_merge( $filter, $status );
			arsort( $otherLanguages );
			$outputLanguages = array_merge( $outputLanguages, $otherLanguages );
		}

		$total = count( $outputLanguages );

		// Maximum of 4 languages to be displayed in the language bar
		if ( $total >= 4 ) {
			// For sending to ULS
			$ulsLanguages = array_keys( $outputLanguages );

			// The current language code needs to be one of those displayed in language bar
			// If not displayed based on the sort, display as last value
			$currentCode = $parser->getTitle()->getPageLanguage()->getCode();
			$key = array_search( $currentCode, array_keys( $outputLanguages ) );

			if ( $key >= 4 ) {
				$languages = array_merge(
					array_slice( $outputLanguages, 0, 3 ),
					array( $currentCode => $status[$currentCode] )
				);

				$ulsLanguages = array_diff( $ulsLanguages, array( $currentCode ) );
				$start = 3;
			} else {
				// First 4 languages form the language bar
				$languages = array_slice( $outputLanguages, 0, 4 );
				$start = 4;
			}

			// Populate ULS with remaining languages as quicklinks
			$parser->getOutput()->addJsConfigVars(
				'wgCommonLanguages',
				array_slice( $ulsLanguages, $start, $total )
			);
			// Page info required to make links in JS
			$parser->getOutput()->addJsConfigVars( 'wgPageBaseTitle',
				$page->getTitle()->getText()
			);
			$parser->getOutput()->addJsConfigVars( 'wgMessageGroupId',
				$page->getMessageGroup()->getId()
			);
		} else {
			$languages = $outputLanguages;
		}
		return array( $total, $languages );
	}
}
