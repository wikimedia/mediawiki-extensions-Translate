/**
 * Dialog for displaying possible renamed messages.
 * Note that methods are not safe to call before the dialog has initialized.
 *
 * @copyright See AUTHORS.txt
 * @license GPL-2.0-or-later
 *
 * @class
 * @extends OO.ui.ProcessDialog
 *
 * @constructor
 * @param {Object} [config] Configuration options
 */
mw.translate = mw.translate || {};

mw.translate.MessageRenameDialog = function ( config ) {
	var errMsg;
	mw.translate.MessageRenameDialog.super.call( this, config );
	this.onRenameSelect = config.onRenameSelect;
	if ( !this.onRenameSelect ) {
		errMsg = 'Must provide the "onRenameSelect" callback function.';
		mw.log.error( errMsg );
		throw new Error( errMsg );
	}

	this.resetProperties();
};

/* Inheritance */
OO.inheritClass( mw.translate.MessageRenameDialog, OO.ui.ProcessDialog );

/* Static Properties */
mw.translate.MessageRenameDialog.static.name = 'MessageRenameDialog';

mw.translate.MessageRenameDialog.static.actions = [
	{
		flags: 'primary',
		label: mw.msg( 'translate-smg-rename-select' ),
		action: 'select',
		active: true
	},
	{
		flags: 'safe',
		label: mw.msg( 'translate-smg-rename-cancel' ),
		action: 'cancel'
	}
];

/**
 * @inheritdoc
 */
mw.translate.MessageRenameDialog.prototype.initialize = function () {
	mw.translate.MessageRenameDialog.super.prototype.initialize.call( this );

	this.messageSearch = new OO.ui.TextInputWidget( {
		placeholder: mw.msg( 'translate-smg-rename-search' )
	} );

	this.searchButton = new OO.ui.ButtonWidget( {
		icon: 'search',
		invisibleLabel: true
	} );

	this.panel = new OO.ui.PanelLayout( {
		padded: true,
		expanded: false
	} );

	this.form = new OO.ui.FormLayout( {
		padded: true,
		expanded: false,
		items: [
			new OO.ui.ActionFieldLayout( this.messageSearch, this.searchButton, {
				classes: [ 'smg-rename-msg-search' ]
			} )
		],
		method: 'post'
	} );

	this.$notice = $( '<p>' )
		.addClass( 'smg-rename-notice hide' );

	this.form.$element.append( this.$notice );
	this.panel.$element.append( this.form.$element );
	this.$body.append( this.panel.$element );

	this.addEvents();
};

mw.translate.MessageRenameDialog.prototype.addEvents = function () {
	this.form.$element.on( 'click', '.smg-rename-list', this.selectMessage.bind( this ) );
	this.messageSearch.on( 'change', OO.ui.debounce( this.filterMessages.bind( this ), 300 ) );
};

/**
 * @inheritdoc
 */
mw.translate.MessageRenameDialog.prototype.getSetupProcess = function ( data ) {
	data = data || {};
	return mw.translate.MessageRenameDialog.super.prototype.getSetupProcess.call( this, data )
		.next( function () {
			// Set up contents based on data
			this.possibleRenames = data.messages;
			this.currentGroupId = data.groupId;
			this.languageCode = data.languageCode;
			this.targetKey = data.targetKey;
			this.selectedMessage = null;

			this.displayMessages( this.possibleRenames );
		}, this );
};

/**
 * @inheritdoc
 */
mw.translate.MessageRenameDialog.prototype.getActionProcess = function ( action ) {
	var renameData, self;
	if ( action === 'cancel' ) {
		return new OO.ui.Process( function () {
			this.close();
			this.emit( action );
		}, this );
	} else if ( action === 'select' ) {
		self = this;
		if ( !this.selectedMessage ) {
			return new OO.ui.Process( function () {
				self.displayNotice( mw.msg( 'translate-smg-rename-select-err' ), 'error' );
			} );
		}
		return mw.translate.MessageRenameDialog.super.prototype.getActionProcess.call( this, action )
			.next( function () {
				// Perform the rename
				var deferred, promise;
				deferred = $.Deferred();
				promise = deferred.promise();

				renameData = {
					groupId: self.currentGroupId,
					languageCode: self.languageCode,
					targetKey: self.targetKey,
					selectedKey: self.selectedMessage
				};

				self.onRenameSelect( renameData ).done( function () {
					return deferred.resolve();
				} ).fail( function ( code, result ) {
					if ( result.error ) {
						if ( result.error.code === 'permissiondenied' ) {
							return deferred.reject( new OO.ui.Error( result.error.info,
								{ recoverable: false } ) );
						}

						return deferred.reject( OO.ui.Error( result.error.info ) );
					}
				} );

				return promise;
			} ).next( function () {
				// Close the dialog
				return self.close();
			} );
	}

	return mw.translate.MessageRenameDialog.super.prototype.getActionProcess.call( this, action );
};

/**
 * @inheritdoc
 */
mw.translate.MessageRenameDialog.prototype.getTeardownProcess = function ( data ) {
	return mw.translate.MessageRenameDialog.super.prototype.getTeardownProcess.call( this, data )
		.first( function () {
			// Perform any cleanup as needed
			this.clearMessages();
			this.messageSearch.setValue( '' );

			this.resetProperties();
		}, this );
};

/**
 * Displays the given messages on the dialog box.
 * @param {Array} messages
 */
mw.translate.MessageRenameDialog.prototype.displayMessages = function ( messages ) {
	var i;
	if ( !messages.length ) {
		this.displayNotice( mw.msg( 'translate-smg-rename-no-msg' ), 'info' );
		return;
	}

	for ( i = 0; i < messages.length; i++ ) {
		this.displayMessage( messages[ i ] );
	}
};

/**
 * Generates the HTML to display a single message
 * @param {Object} message
 */
mw.translate.MessageRenameDialog.prototype.displayMessage = function ( message ) {
	var $title, $content, $container;

	$title = $( '<div>' ).append(
		$( '<a>' ).text( message.title ).addClass( 'smg-rename-msg-key' )
			.attr( 'href', message.link )
			.data( 'msg-key', message.key ),
		$( '<span>' ).text( message.similarity + '%' ).addClass( 'smg-rename-similarity' )
	);

	$content = $( '<div>' ).text( message.content ).addClass( 'smg-rename-msg-content' );

	$container = $( '<div>' ).addClass( 'smg-rename-list' );

	$container.append( $title, $content );

	this.form.$element.append( $container );
};

/**
 * Callback triggered when a message is selected.
 * @param {Object} event
 */
mw.translate.MessageRenameDialog.prototype.selectMessage = function ( event ) {
	var $target = $( event.currentTarget );
	this.selectedMessage = $target.find( '.smg-rename-msg-key' ).data( 'msgKey' );

	this.form.$element.find( '.smg-rename-list' ).removeClass( 'smg-rename-selected' );
	$target.addClass( 'smg-rename-selected' );

	this.hideNotice();
};

/**
 * Used to reset the state properties for the dialog box.
 */
mw.translate.MessageRenameDialog.prototype.resetProperties = function () {
	this.possibleRenames = [];
	this.currentGroupId = null;
	this.languageCode = null;
	this.targetKey = null;
	this.selectedMessage = null;
};

/**
 * Callback function triggered to handle the search.
 * @param {Object} searchValue
 */
mw.translate.MessageRenameDialog.prototype.filterMessages = function ( searchValue ) {
	var normalizedSearchVal = searchValue.toLowerCase(), filteredMessages = [];

	// if the dialog is closing, lets not do anything.
	if ( this.isClosing() || !this.isVisible() ) {
		return;
	}

	filteredMessages = this.possibleRenames.filter( function ( message ) {
		if ( message.key.toLowerCase().indexOf( normalizedSearchVal ) !== -1 ) {
			return true;
		}

		if ( message.content.toLowerCase().indexOf( normalizedSearchVal ) !== -1 ) {
			return true;
		}
	} );

	this.clearMessages();
	this.displayMessages( filteredMessages );
	this.updateSize();
};

mw.translate.MessageRenameDialog.prototype.displayNotice = function ( msg, type ) {
	var possibleTypes = [ 'info', 'error', 'warning' ];
	this.$notice.removeClass( possibleTypes.join( ' ' ) );
	this.$notice.text( msg ).addClass( type ).removeClass( 'hide' );
	this.updateSize();
};

mw.translate.MessageRenameDialog.prototype.hideNotice = function () {
	this.$notice.addClass( 'hide' );
	this.updateSize();
};

/**
 * Clears all messages from the DOM.
 */
mw.translate.MessageRenameDialog.prototype.clearMessages = function () {
	this.form.$element.find( '.smg-rename-list' ).remove();
	this.hideNotice();
};
