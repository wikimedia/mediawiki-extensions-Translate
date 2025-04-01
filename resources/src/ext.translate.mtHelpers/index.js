/**
 * This code is adapted from ContentTranslation
 * Source: [https://gerrit.wikimedia.org/g/mediawiki/extensions/ContentTranslation/+/cfa4f6bc825446020e00c52e2a8bced31cc55839/app/src/utils/mtHelper.js]
 * License: GPLv2 (GNU General Public License, version 2)
 */

'use strict';

const CJKLanguages = [
	'cjy-hans',
	'cjy-hant',
	'gan-hans',
	'gan',
	'hak-hans',
	'hak-hant',
	'hsn',
	'ii',
	'ja',
	'jje',
	'ko-kp',
	'ko',
	'lzh',
	'ryu',
	'wuu',
	'yue',
	'zh',
	'zh-cn',
	'zh-hans',
	'zh-hant',
	'zh-hk',
	'zh-mo',
	'zh-my',
	'zh-sg',
	'zh-tw'
];

/**
 * Tokenize a given string. Here tokens is basically words for non CJK languages.
 * For CJK languages, we just split at each codepoint level.
 *
 * @param {string} string
 * @param {string} language
 * @return {string[]}
 */
const tokenize = function ( string, language ) {
	if ( !string ) {
		return [];
	}
	if ( CJKLanguages.includes( language ) ) {
		return string.split( '' );
	}
	// Match all non whitespace characters for tokens.
	return string.match( /\S+/g ) || [];
};

const calculateUnmodifiedContent = {

	/**
	 * A very simple method to calculate the difference between two strings in the scale
	 * of 0 to 1, based on relative number of tokens changed in string2 from string1.
	 *
	 * @param {string} string1
	 * @param {string} string2
	 * @param {string} language
	 * @return {number} A value between 0 and 1
	 */
	calculateUnmodifiedContent: function ( string1, string2, language ) {
		let bigSet, smallSet, tokens1, tokens2;
		if ( !string1 || !string2 ) {
			return 0;
		}
		if ( string1 === string2 ) {
			// Both strings are equal. So string2 is 100% unmodified version of string1
			return 1;
		}
		bigSet = tokens1 = tokenize( string1, language );
		smallSet = tokens2 = tokenize( string2, language );
		if ( tokens2.length > tokens1.length ) {
			// Swap the sets
			bigSet = tokens2;
			smallSet = tokens1;
		}
		// Find the intersection (tokens that did not change) of two token sets
		const unmodifiedTokens = bigSet.filter( ( token ) => smallSet.includes( token ) );
		// If string1 has 10 tokens and we see that 2 tokens are different or not present in
		// string2, we are saying that string2 is 80% (ie. 10-2/10) of unmodified version
		// for string1.
		return unmodifiedTokens.length / bigSet.length;
	}
};

module.exports = calculateUnmodifiedContent;
