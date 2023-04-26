/**
 * @class
 * @extends OO.ui.TextInputWidget
 * @mixins OO.ui.mixin.LookupElement
 *
 * @author Abijeet Patro
 * @license GPL-2.0-or-later
 * @since 2022.02
 * @constructor
 * @param {Object} [config] Configuration options
 * @cfg {Function} [onFail] Callback function triggered when an error occurs
 * @cfg {Function} [onSelect] Callback function triggered when an item is selected
 * @cfg {Array} [entityType] Entity type to query for - "groups" and/or "messages"
 */
var EntitySelectorWidget = function ( config ) {
	// Parent constructor
	OO.ui.TextInputWidget.call( this, {
		classes: [ 'tes-entity-selector' ],
		placeholder: mw.msg( 'translate-tes-type-to-search' ),
		autocomplete: config.autocomplete || false
	} );
	// Mixin constructors
	OO.ui.mixin.LookupElement.call( this );

	this.entityNotFound = new OO.ui.MenuOptionWidget( {
		label: mw.msg( 'translate-tes-entity-not-found' ),
		disabled: true,
		highlightable: false,
		pressable: false,
		icon: 'info',
		classes: [ 'tes-optgroup-label' ]
	} );

	this.messageLabel = new OO.ui.MenuOptionWidget( {
		label: mw.msg( 'translate-tes-optgroup-message' ),
		disabled: true,
		highlightable: false,
		pressable: false,
		classes: [ 'tes-optgroup-label' ]
	} );

	this.groupLabel = new OO.ui.MenuOptionWidget( {
		label: mw.msg( 'translate-tes-optgroup-group' ),
		disabled: true,
		highlightable: false,
		pressable: false,
		classes: [ 'tes-optgroup-label' ]
	} );

	this.errorLabel = new OO.ui.MenuOptionWidget( {
		disabled: true,
		highlightable: false,
		pressable: false,
		icon: 'error',
		classes: [ 'tes-error-label' ]
	} );

	var noop = function () {};
	this.failureCallback = config.onFail || noop;
	this.selectCallback = config.onSelect || noop;
	this.entityTypeToFetch = config.entityType;
	this.inputId = config.inputId || null;
	if ( this.entityTypeToFetch && !Array.isArray( this.entityTypeToFetch ) ) {
		throw new Error( 'entityType must be an array.' );
	}

	this.selectedEntity = null;
	if ( this.inputId ) {
		this.$input.attr( 'id', this.inputId );
	}
};

OO.inheritClass( EntitySelectorWidget, OO.ui.TextInputWidget );
OO.mixinClass( EntitySelectorWidget, OO.ui.mixin.LookupElement );

EntitySelectorWidget.prototype.getLookupRequest = function () {
	var value = this.getValue();
	var widget = this;

	if ( value === '' ) {
		return $.Deferred().resolve( [] );
	}

	// Detect if there is an existing  request pending
	// and abort it if it is.
	if ( this.isPending() ) {
		this.abortLookupRequest();
	}

	var deferred = $.Deferred();
	var currentRequestTimeout = setTimeout(
		function () {
			currentRequestTimeout = null;
			makeRequest( value, widget.entityTypeToFetch, deferred, widget.failureCallback );
		},
		250
	);

	deferred.abort = function () {
		clearTimeout( currentRequestTimeout );
		currentRequestTimeout = null;
		// Stop showing the loader
		widget.popPending();
	};

	return deferred;
};

function makeRequest( value, entityType, deferred, cbFailure ) {
	var api = new mw.Api();
	api.get( {
		action: 'translationentitysearch',
		query: value,
		entitytype: entityType
	} ).then( function ( result ) {
		deferred.resolve( result.translationentitysearch );
	}, function ( msg, error ) {
		mw.log.error( error );
		cbFailure( error, mw.msg( 'translate-tes-server-error' ) );
		deferred.resolve( error );
	} );
}

EntitySelectorWidget.prototype.getLookupMenuOptionsFromData = function ( response ) {
	var groups = response.groups || [];
	var messages = response.messages || [];
	var finalResult = [];
	var i = 0;

	if ( response && response.error ) {
		this.errorLabel.setLabel( mw.msg( 'translate-tes-server-error' ) );
		finalResult.push( this.errorLabel );
		return finalResult;
	}

	if ( groups.length ) {
		// Only add the label if both entities: groups and messages are to be fetched
		if ( this.entityTypeToFetch.length !== 1 ) {
			finalResult.push( this.groupLabel );
		}

		for ( ; i < groups.length; ++i ) {
			finalResult.push(
				new OO.ui.MenuOptionWidget( {
					data: {
						type: 'group',
						data: groups[ i ].group
					},
					label: groups[ i ].label
				} )
			);
		}
	}
	if ( messages.length ) {
		// Only add the label if both entities: groups and messages are to be fetched
		if ( this.entityTypeToFetch.length !== 1 ) {
			finalResult.push( this.messageLabel );
		}

		for ( i = 0; i < messages.length; ++i ) {
			var messageOption = new OO.ui.MenuOptionWidget( {
				data: {
					type: 'message',
					data: messages[ i ].pattern
				},
				label: messages[ i ].pattern
			} );
			if ( messages[ i ].count > 1 ) {
				messageOption.$element.append(
					$( '<span>' )
						.text( mw.msg( 'translate-tes-message-prefix', messages[ i ].count ) )
						.addClass( 'tes-message-subtext' )
				);
			}
			finalResult.push( messageOption );
		}
	}

	if ( !finalResult.length ) {
		finalResult.push( this.entityNotFound );
	}

	return finalResult;
};

EntitySelectorWidget.prototype.getLookupCacheDataFromResponse = function ( response ) {
	return response || [];
};

/**
 * Override the LookupElement method to use the label as selected value instead
 * of data.
 *
 * @param {OO.ui.MenuOptionWidget} item
 */
EntitySelectorWidget.prototype.onLookupMenuChoose = function ( item ) {
	this.selectedEntity = item;
	this.setValue( item.getLabel() );
	this.selectCallback( item.getData() );
};

EntitySelectorWidget.prototype.getSelectedEntity = function () {
	return this.selectedEntity.getData();
};

module.exports = EntitySelectorWidget;
