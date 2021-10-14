'use strict';
/* eslint-disable no-implicit-globals */

/**
 * @class
 * @extends OO.ui.MenuTagMultiselectWidget
 * @mixins OO.ui.mixin.RequestManager
 * @mixins OO.ui.mixin.PendingElement
 *
 * @constructor
 * @param {Object} [config] Configuration options
 *
 * @author Niklas Laxström
 * @license GPL-2.0-or-later
 * @since 2020.07
 */
var LanguagesMultiselectWidget = function ( config ) {
	this.api = config.api;
	this.allowedValues = Object.keys( config.languages );
	this.languages = config.languages;

	LanguagesMultiselectWidget.parent.call( this, $.extend( true,
		{
			allowEditTags: false,
			allowArbitary: false,
			menu: {
				filterFromInput: false
			}
		},
		config
	) );

	OO.ui.mixin.RequestManager.call( this, config );
	OO.ui.mixin.PendingElement.call( this, $.extend( true, {}, config, {
		$pending: this.$handle
	} ) );

	// No need for default values for the menu
	this.menu.clearItems();
};

/* Setup */

OO.inheritClass( LanguagesMultiselectWidget, OO.ui.MenuTagMultiselectWidget );
OO.mixinClass( LanguagesMultiselectWidget, OO.ui.mixin.RequestManager );
OO.mixinClass( LanguagesMultiselectWidget, OO.ui.mixin.PendingElement );

/* Methods */

/** @inheritdoc OO.ui.MenuTagMultiselectWidget */
LanguagesMultiselectWidget.prototype.onInputChange = function () {
	var widget = this;

	this.getRequestData()
		.then( function ( data ) {
			// Reset
			widget.menu.clearItems();
			widget.menu.addItems( widget.getOptionsFromData( data ) );
		} ).always( function () {
			// Parent method
			LanguagesMultiselectWidget.parent.prototype.onInputChange.call( widget );
		} );
};

/**
 * Get option widgets from the server response
 *
 * @param {Object} data Query result
 * @return {OO.ui.MenuOptionWidget[]} Menu items
 */
LanguagesMultiselectWidget.prototype.getOptionsFromData = function ( data ) {
	var options = [];

	for ( var languageCode in data ) {
		if ( this.languages[ languageCode ] !== undefined ) {
			options.push( new OO.ui.MenuOptionWidget( {
				data: languageCode,
				label: this.languages[ languageCode ]
			} ) );
		}
	}

	return options;
};

/** @inheritdoc OO.ui.MenuTagMultiselectWidget */
LanguagesMultiselectWidget.prototype.setValue = function ( valueObject ) {
	valueObject = Array.isArray( valueObject ) ? valueObject : [ valueObject ];

	this.clearItems();
	valueObject.forEach( function ( obj ) {
		var data;
		if ( typeof obj === 'string' ) {
			data = obj;
		} else {
			data = obj.data;
		}

		this.addTag( data, this.languages[ data ] );
	}.bind( this ) );
};

/** @inheritdoc OO.ui.mixin.RequestManager */
LanguagesMultiselectWidget.prototype.getRequestQuery = function () {
	return this.input.getValue();
};

/** @inheritdoc OO.ui.mixin.RequestManager */
LanguagesMultiselectWidget.prototype.getRequest = function () {
	return this.api.get( {
		action: 'languagesearch',
		formatversion: '2',
		search: this.getRequestQuery()
	} );
};

/** @inheritdoc OO.ui.mixin.RequestManager */
LanguagesMultiselectWidget.prototype.getRequestCacheDataFromResponse = function ( response ) {
	return response.languagesearch || {};
};

module.exports = LanguagesMultiselectWidget;
