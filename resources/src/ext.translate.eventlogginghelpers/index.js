( function () {
	'use strict';

	// Machine-readable name of the instrument registered in Test Kitchen.
	// The instrument encapsulates the destination stream and the event schema
	// so neither needs to be specified at the call site.
	const INSTRUMENT_NAME = 'translate-click-actions';
	const config = require( './config.json' );

	let instrument;

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
			this.logEvent( 'click', actionSubtype, actionSource, translation );
		},

		/**
		 * @param {string} action
		 * @param {string|null} actionSubtype
		 * @param {string|null} actionSource
		 * @param {Object|null} translation
		 */
		logEvent: function ( action, actionSubtype, actionSource, translation ) {
			// Cheap config gate first, so getInstrument() is skipped entirely when
			// event logging is disabled. Test Kitchen is a soft dependency: it
			// loads its own SDK (mw.testKitchen) when installed and enabled, so we
			// no-op when it is unavailable.
			if ( !this.isEventLoggingEnabled() || !mw.testKitchen ) {
				return;
			}

			if ( !instrument ) {
				instrument = mw.testKitchen.getInstrument( INSTRUMENT_NAME );
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

			instrument.send( action, interactionData );
		}
	};

	module.exports = eventLoggingHelpers;
}() );
