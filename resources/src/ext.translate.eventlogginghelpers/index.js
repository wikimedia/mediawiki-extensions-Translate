( function () {
	'use strict';

	const streamName = 'mediawiki.product_metrics.translate_extension';
	const schemaId = '/analytics/product_metrics/web/translation/1.0.0';
	const config = require( './config.json' );

	const eventLoggingHelpers = {
		/**
		 * @param {string|null} actionSubtype
		 * @param {string|null} actionSource
		 * @param {Object|null} actionContext
		 */
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

			if ( actionContext ) {
				// action_context is defined as a string under
				// https://gitlab.wikimedia.org/repos/data-engineering/metrics-platform/-/blob/fcdc361d04792930e5b10f0fd6bd1f3150f34737/js/src/EventData.d.ts#L104
				// eslint-disable-next-line camelcase
				interactionData.action_context = JSON.stringify( actionContext );
			}

			mw.eventLog.submitClick( streamName, interactionData );
		},

		/**
		 * @param {string} action
		 * @param {string|null} actionSubtype
		 * @param {string|null} actionSource
		 * @param {Object|null} actionContext
		 */
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

			if ( actionContext ) {
				// action_context is defined as a string under
				// https://gitlab.wikimedia.org/repos/data-engineering/metrics-platform/-/blob/fcdc361d04792930e5b10f0fd6bd1f3150f34737/js/src/EventData.d.ts#L104
				// eslint-disable-next-line camelcase
				interactionData.action_context = JSON.stringify( actionContext );
			}

			mw.eventLog.submitInteraction( streamName, schemaId, action, interactionData );
		}
	};

	module.exports = eventLoggingHelpers;
}() );
