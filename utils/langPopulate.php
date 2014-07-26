<?php

class langPopulate {
	static function langPopulateOrder ( Parser $parser, TranslatablePage $page ) {
		// TODO Geoclient based data, user preferences based data, user history based data
		$status = $page->getTranslationPercentages();

		// Current language comes first
		$code = $page->getTitle()->getPageLanguage()->getCode();
		$output = array( $code => $status[$code] );

		// Priority languages come next
		$priorityLangs = TranslateMetadata::get( $page->getMessageGroupId(), 'prioritylangs' );
		$priorityForce = TranslateMetadata::get( $page->getMessageGroupId(), 'priorityforce' );
		$filter = array();
		if ( strlen( $priorityLangs ) > 0 ) {
			$filter = array_flip( explode( ',', $priorityLangs ) );
		}

		// Get the percentages for all filter languages if available
		$filter = array_intersect( $filter, $status );
		$output = array_merge( $output, $filter );

		// Set percentage complete as 0 in case of translation not started
		foreach ( $output as $code => $perc ) {
			if ( !isset( $perc ) ) {
				$output[$perc] = 0;
			}
		}

		// Now sort the languages by percentage of translation complete
		arsort( $status );
		$output = array_merge( $output, $status );

		// First 4 languages form the translate bar
		if ( count( $output ) >= 4 ) {
			$r = array();
			foreach ( $output as $code => $value ) {
				$r[] = $code;
			}

			// Populate ULS with remaining languages as quicklinks
			$parser->getOutput()->addJsConfigVars( 'wgCommonLanguages', array_slice( $r, 4, count( $output ) ) )  ;
			$parser->getOutput()->addJsConfigVars( 'wgPageBaseTitle', $page->getTitle()->getText() );
			$parser->getOutput()->addJsConfigVars( 'wgMessageGroupId', $page->getMessageGroup()->getId() );

			// First 4 languages form the language bar
			return array_slice( $output, 0, 4 );
		} else {
			// Get common languages using GeoClient?
		}
	}
}
