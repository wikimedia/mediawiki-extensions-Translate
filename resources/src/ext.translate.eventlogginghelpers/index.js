( function () {
	'use strict';

	const streamName = 'mediawiki.product_metrics.translate_extension';
	const schemaId = '/analytics/product_metrics/web/base/1.2.0';
	const config = require( './config.json' );

	const eventLoggingHelpers = {
		/**
		 * @param {string|null} actionSubtype
		 * @param {string|null} actionSource
		 * @param {Object|null} actionContext
		 */
		// eslint-disable-next-line no-unused-vars
		logClickEvent: function ( actionSubtype, actionSource, actionContext ) {
			if ( !config.TranslateEnableEventLogging ) {
				return;
			}

			const interactionData = {};
			if ( actionSubtype ) {
				// eslint-disable-next-line camelcase
				interactionData.action_subtype = actionSubtype;
			}

			if ( actionSource ) {
				// eslint-disable-next-line camelcase
				interactionData.action_source = actionSource;
			}
			/*
			Implementation can be revisited after T369687 is resolved.
			if (actionContext) {
				// action_context is defined as a string under
				// https://gitlab.wikimedia.org/repos/data-engineering/metrics-platform/-/blob/fcdc361d04792930e5b10f0fd6bd1f3150f34737/js/src/EventData.d.ts#L104
				interactionData.action_context = JSON.stringify( actionContext );
			}
			*/
			mw.eventLog.submitClick( streamName, interactionData );
		},

		/**
		 * @param {string} action
		 * @param {string|null} actionSubtype
		 * @param {string|null} actionSource
		 * @param {Object|null} actionContext
		 */
		// eslint-disable-next-line no-unused-vars
		logEvent: function ( action, actionSubtype, actionSource, actionContext ) {
			if ( !config.TranslateEnableEventLogging ) {
				return;
			}

			const interactionData = {};
			if ( actionSubtype ) {
				// eslint-disable-next-line camelcase
				interactionData.action_subtype = actionSubtype;
			}

			if ( actionSource ) {
				// eslint-disable-next-line camelcase
				interactionData.action_source = actionSource;
			}
			/*
			Implementation can be revisited after T369687 is resolved.
			if ( actionContext ) {
				// action_context is defined as a string under
				// https://gitlab.wikimedia.org/repos/data-engineering/metrics-platform/-/blob/fcdc361d04792930e5b10f0fd6bd1f3150f34737/js/src/EventData.d.ts#L104
				// eg interactionData.action_context = JSON.stringify( actionContext );
				// eslint-disable-next-line camelcase
				interactionData.action_context = null;
			}
			*/
			mw.eventLog.submitInteraction( streamName, schemaId, action, null );
		}
	};

	module.exports = eventLoggingHelpers;
}() );
