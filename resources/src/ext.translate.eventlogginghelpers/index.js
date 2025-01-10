( function () {
	'use strict';

	const streamName = 'mediawiki.product_metrics.translate_extension';
	const schemaId = '/analytics/product_metrics/web/translation/1.0.0';
	const config = require( './config.json' );

	const eventLoggingHelpers = {
		isEventLoggingEnabled: function () {
			return config.TranslateEnableEventLogging;
		},

		/**
		 * @param {string|null} actionSubtype
		 * @param {string|null} actionSource
		 * @param {Object|null} translation
		 */
		logClickEvent: function ( actionSubtype, actionSource, translation ) {
			if ( !this.isEventLoggingEnabled() ) {
				return;
			}

			const interactionData = {
				translation: translation || {}
			};

			if ( actionSubtype ) {
				// eslint-disable-next-line camelcase
				interactionData.action_subtype = actionSubtype;
			}

			if ( actionSource ) {
				// eslint-disable-next-line camelcase
				interactionData.action_source = actionSource;
			}

			mw.eventLog.submitInteraction( streamName, schemaId, 'click', interactionData );
		},

		/**
		 * @param {string} action
		 * @param {string|null} actionSubtype
		 * @param {string|null} actionSource
		 * @param {Object|null} translation
		 */
		logEvent: function ( action, actionSubtype, actionSource, translation ) {
			if ( !config.TranslateEnableEventLogging ) {
				return;
			}

			const interactionData = {
				translation: translation || {}
			};

			if ( actionSubtype ) {
				// eslint-disable-next-line camelcase
				interactionData.action_subtype = actionSubtype;
			}

			if ( actionSource ) {
				// eslint-disable-next-line camelcase
				interactionData.action_source = actionSource;
			}

			mw.eventLog.submitInteraction( streamName, schemaId, action, interactionData );
		}
	};

	module.exports = eventLoggingHelpers;
}() );
