( function () {
	'use strict';

	const config = require( './ext.translate.pagetranslation.uls.config.json' );

	/**
	 * Overrides the mw.uls.changeLanguage present in ULS
	 *
	 * @internal
	 * @param {string} language
	 */
	mw.uls.changeLanguage = function ( language ) {
		var page = 'Special:MyLanguage/' + mw.config.get( 'wgPageName' );

		if ( config.TranslatePageTranslationULS === 'translation' ) {
			page = page.replace( /\/[^/]+$/, '' );
		}

		mw.uls.setLanguage( language ).then( function () {
			location.href = mw.util.getUrl( page );
		} );
	};

	const EntrypointRegistry = require( 'ext.uls.rewrite.entrypoints' );
	const { cdxIconAdd } = require( './ext.translate.pagetranslation.uls.icons.json' );
	const { supportedLanguages } = require( 'ext.translate.languages' );
	const priorityLanguages = mw.config.get( 'wgTranslatePriorityLanguages' ) || [];
	const groupId = mw.config.get( 'wgTranslatePageTranslationGroup' );

	EntrypointRegistry.register( 'empty-search', {
		id: 'translate-empty-search-recommendation',
		shouldShow: ( context ) => {
			const hitCodes = Object.keys( context.searchQueryHits || {} );
			return hitCodes.some( ( code ) => {
				const isAllowedByPriority = priorityLanguages.length === 0 || priorityLanguages.includes( code );
				return isAllowedByPriority && !!supportedLanguages[ code ];
			} );
		},
		getConfig: ( context ) => {
			const hitCodes = Object.keys( context.searchQueryHits || {} );
			const suggestionCodes = context.suggestions || [];

			const codes = [ ...new Set( [ ...hitCodes, ...suggestionCodes ] ) ]
				.filter( ( code ) => {
					const isAllowedByPriority = priorityLanguages.length === 0 || priorityLanguages.includes( code );
					return isAllowedByPriority && !!supportedLanguages[ code ];
				} )
				.slice( 0, 3 );

			return codes.map( ( code ) => ( {
				label: supportedLanguages[ code ],
				description: mw.msg( 'ext-uls-empty-search-entrypoint-description' ),
				icon: cdxIconAdd,
				url: mw.util.getUrl( 'Special:Translate', {
					group: groupId,
					language: code
				} )
			} ) );
		}
	}, 'content' );

	EntrypointRegistry.register( 'missing-languages', {
		id: 'translate-missing-languages-recommendation',
		shouldShow: ( context ) => {
			const missingLanguages = context.missingLanguages || [];
			return missingLanguages.some( ( code ) => {
				const isAllowedByPriority = priorityLanguages.length === 0 || priorityLanguages.includes( code );
				return isAllowedByPriority && !!supportedLanguages[ code ];
			} );
		},
		getConfig: ( context ) => {
			const missingLanguages = context.missingLanguages || [];
			const codes = missingLanguages.filter( ( code ) => {
				const isAllowedByPriority = priorityLanguages.length === 0 || priorityLanguages.includes( code );
				return isAllowedByPriority && !!supportedLanguages[ code ];
			} );

			return codes.map( ( code ) => ( {
				label: supportedLanguages[ code ],
				description: mw.msg( 'ext-uls-missing-languages-entrypoint-description' ),
				icon: cdxIconAdd,
				url: mw.util.getUrl( 'Special:Translate', {
					group: groupId,
					language: code
				} )
			} ) );
		}
	}, 'content' );
}() );
