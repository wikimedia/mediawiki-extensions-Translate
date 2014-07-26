<?php
/**
 * Class to populate the language bar
 *
 * @author Kunal Grover
 * @license Public Domain
 * @file
 */

class LangPopulate {
	static function langPopulateOrder( Parser $parser, TranslatablePage $page ) {
		$status = $page->getTranslationPercentages();

		// Current language comes first
		$sourceLanguageCode = $page->getSourceLanguageCode();
		$outputLanguages = array( $sourceLanguageCode => $status[$sourceLanguageCode] );

		// Load all priority languages
		$priorityLangs = TranslateMetadata::get( $page->getMessageGroupId(), 'prioritylangs' );
		$filter = array();
		if ( strlen( $priorityLangs ) > 0 ) {
			$filter = array_flip( explode( ',', $priorityLangs ) );

			// If translation not started, we want priority languages to still be displayed with
			// 0% complete
			foreach ( $filter as $code => $p ) {
				if ( !isset( $status[$code] ) ) {
					$status[$code] = 0;
				}
				$filter[$code] = $status[$code];
			}

			// Get the percentages for all filter languages if available
			$filter = array_diff( $status, $status );
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
		$select = '';

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

			$otherLanguages = array_slice( $ulsLanguages, $start, $total );
			// Populate ULS with remaining languages as quicklinks
			$parser->getOutput()->addJsConfigVars( 'wgCommonLanguages', $otherLanguages );
			// Page info required to make links in JS
			$parser->getOutput()->addJsConfigVars( 'wgPageBaseTitle',
				$page->getTitle()->getText()
			);
			$parser->getOutput()->addJsConfigVars( 'wgMessageGroupId',
				$page->getMessageGroup()->getId()
			);

			// Building a no JS fallback selector
			$select = Html::openElement( 'ul', array(
				'class' => 'mw-translate-options',
				'id' => 'mw-more-languages'
			) );
			$select .= '<span class="caret-before"></span>
				<span class="caret-after"></span>';

			$userLangCode = $parser->getOptions()->getUserLang();
			foreach ( $otherLanguages as $code ) {
				$name = TranslateUtils::getLanguageName( $code, $userLangCode );
				$lang = Html::element( 'li', array(
					'class' => "mw-translate-language mw-translate-option"
				), $name );
				$lang = Linker::link( $page->getTitle()->getSubpage( $code ), $lang );
				$select .= $lang;
			}
			$select .= Html::closeElement( 'ul' );

		} else {
			$languages = $outputLanguages;
		}

		return array( $total, $languages, $select );
	}
}
