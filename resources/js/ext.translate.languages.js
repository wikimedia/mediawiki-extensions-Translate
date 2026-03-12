'use strict';

const { supportedLanguages, undeterminedLanguageCode } = require( './languages.json' );
mw.config.set( 'wgTranslateLanguages', supportedLanguages );
mw.config.set( 'wgTranslateUndeterminedLanguageCode', undeterminedLanguageCode );

module.exports = {
	supportedLanguages,
	undeterminedLanguageCode
};
