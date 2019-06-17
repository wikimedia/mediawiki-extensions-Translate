( function () {
	var RenameDropdown;

	$( function () {
		var windowManager, renameDialog;

		RenameDropdown.init();

		/**
		 * Attach the click handler to display the rename dropdown.
		 */
		$( '#mw-content-text' ).on( 'click', '.smg-rename-actions', function ( event ) {
			var $target, $parentContainer;
			$target = $( event.target );
			$parentContainer = $target.parents( '.mw-translate-smg-change' );
			RenameDropdown.appendTo( event.target, $parentContainer, {
				groupId: $target.data( 'groupId' ),
				msgKey: $target.data( 'msgkey' ),
				msgTitle: $target.data( 'msgtitle' )
			} ).show();

			if ( $parentContainer.hasClass( 'smg-change-addition' ) ) {
				RenameDropdown.hideOption( '.smg-rename-new-action' );
			}
			event.preventDefault();
		} );

		$( 'html' ).on( 'click', function ( event ) {
			if ( !event.isDefaultPrevented() ) {
				RenameDropdown.hide();
			}
		} );

		/**
		 * Click handler triggered when "Add as rename" is clicked in the dropdown.
		 */
		$( '.smg-rename-rename-action' ).click( function () {
			var keyData = RenameDropdown.getData();
			getRenames( keyData.groupId, keyData.msgKey )
				.done( function ( data ) {
				// Open the dialog, and display possible renames.
					windowManager.openWindow( renameDialog, {
						messages: data.managemessagegroups[ 0 ],
						title: mw.msg( 'translate-smg-rename-dialog-title', keyData.msgTitle ),
						groupId: keyData.groupId,
						targetKey: keyData.msgKey
					} );
				} ).fail( function ( code, result ) {
					if ( result.error ) {
						mw.notify( result.error.info, {
							type: 'error',
							tag: 'rename-error'
						} );
					}
				} );
		} );

		/**
		 * Click handler triggered when "Add as new" is clicked in the dropdown.
		 */
		$( '.smg-rename-new-action' ).click( function () {
			var keyData = RenameDropdown.getData();
			setAsNew( keyData.groupId, keyData.msgKey ).done( function ( data ) {
				if ( data.managemessagegroups && data.managemessagegroups.success ) {
					window.location.reload();
				}
			} ).fail( function ( code, result ) {
				if ( result.error ) {
					mw.notify( result.error.info, {
						type: 'error',
						tag: 'new-error'
					} );
				}
			} );
		} );

		// Create and append a window manager.
		windowManager = new OO.ui.WindowManager();
		$( document.body ).append( windowManager.$element );

		// Create a new process dialog window.
		renameDialog = new mw.translate.MessageRenameDialog( {
			classes: [ 'smg-rename-dialog' ],
			onRenameSelect: function ( renameParams ) {
				return setRename( renameParams ).done( function ( data ) {
					if ( data.managemessagegroups && data.managemessagegroups.success ) {
						window.location.reload();
					}
				} );
			},
			size: 'large'
		} );

		// Add the window to window manager using the addWindows() method.
		windowManager.addWindows( [ renameDialog ] );
	} );

	/**
	 * Fetch the possible renames for a given message.
	 * @param {string} groupId
	 * @param {string} msgKey
	 * @return {jQuery.Promise}
	 */
	function getRenames( groupId, msgKey ) {
		var params, api = new mw.Api();
		params = {
			action: 'query',
			meta: 'managemessagegroups',
			formatversion: 2,
			mmggroup: groupId,
			mmgmsgkey: msgKey,
			mmggchangesetname: getChangesetName()
		};

		return api.get( params ).then( function ( result ) {
			return result.query;
		} );
	}

	/**
	 * Identifies and returns the group name from the URL
	 * @return {string}
	 */
	function getChangesetName() {
		var locationPaths, suffix, pageTitle;
		locationPaths = window.location.pathname.split( '/' );
		suffix = locationPaths.pop();
		pageTitle = $( '#smgPageTitle' ).val();

		if ( suffix.indexOf( pageTitle ) !== -1 ) {
			return suffix;
		}

		return null;
	}

	function getChangesetModifiedTime() {
		var modifiedTime = $( '[name="changesetModifiedTime"]' ).val();
		modifiedTime = +modifiedTime;
		if ( isNaN( modifiedTime ) ) {
			return 0;
		}

		return modifiedTime;
	}

	/**
	 * Update the rename associated with a message
	 * @param {Object} renameParams
	 * @param {string} renameParams.groupId
	 * @param {string} renameParams.selectedKey Key to be matched to. This message will be renamed.
	 * @param {string} renameParams.targetKey Key from the source
	 * @return {jQuery.Promise}
	 */
	function setRename( renameParams ) {
		var params, api = new mw.Api();

		params = {
			action: 'managemessagegroups',
			groupId: renameParams.groupId,
			renameMsgKey: renameParams.selectedKey,
			msgKey: renameParams.targetKey,
			op: 'rename',
			changesetName: getChangesetName(),
			changesetModified: getChangesetModifiedTime(),
			assert: 'user',
			formatversion: 2
		};

		return api.postWithToken( 'csrf', params );
	}

	/**
	 * Mark the message as a new message
	 * @param {string} groupId
	 * @param {string} msgKey
	 * @return {jQuery.Promise}
	 */
	function setAsNew( groupId, msgKey ) {
		var params, api = new mw.Api();

		params = {
			action: 'managemessagegroups',
			groupId: groupId,
			msgKey: msgKey,
			op: 'new',
			changesetName: getChangesetName(),
			changesetModified: getChangesetModifiedTime(),
			assert: 'user',
			formatversion: 2
		};

		return api.postWithToken( 'csrf', params );
	}

	RenameDropdown = ( function () {
		var $renameMenu;

		/**
		 * Initialization function. Creates the elements for the rename dropdown
		 * @return {RenameDropdown}
		 */
		function init() {
			$renameMenu = getRenameDropdown().appendTo( $( 'body' ) );
			return this;
		}

		/**
		 * Returns the HTML element for the dropdown
		 * @return {jQuery}
		 */
		function getRenameDropdown() {
			var $addAsRename = $( '<li>' ).append(
					$( '<button>' )
						.attr( 'type', 'button' )
						.addClass( 'smg-rename-new-action' )
						.text( mw.msg( 'translate-smg-rename-new' ) )
				),
				$addAsNew = $( '<li>' ).append(
					$( '<button>' )
						.attr( 'type', 'button' )
						.addClass( 'smg-rename-rename-action' )
						.text( mw.msg( 'translate-smg-rename-rename' ) )
				);

			return $( '<ul>' ).addClass( 'smg-rename-dropdown-menu' ).append(
				$addAsRename,
				$addAsNew
			);
		}

		/**
		 * Displays the rename menu
		 * @return {RenameDropdown}
		 */
		function show() {
			$renameMenu.addClass( 'show' );
			return this;
		}

		/**
		 * Hides the rename menu
		 * @return {RenameDropdown}
		 */
		function hide() {
			$renameMenu.removeClass( 'show' );
			return this;
		}

		/**
		 * Appends the dropdown to a container element
		 * @param {jQuery} target Target trigger element
		 * @param {jQuery} $container Container to which to append the menu
		 * @param {Object} customData Custom data to be associated with the menu
		 * @return {RenameDropdown}
		 */
		function appendTo( target, $container, customData ) {
			var $target = $( target );
			$container.append( $renameMenu );
			$renameMenu.css( {
				top: $target.position().top + $target.height(),
				left: $target.position().left - $renameMenu.width() + $target.width()
			} ).data( 'custom-data', customData );

			// show all the li's
			$renameMenu.find( 'li' ).show();
			return this;
		}

		/**
		 * Fetch the custom data associated with rename menu
		 * @return {Object}
		 */
		function getData() {
			return $renameMenu.data( 'custom-data' );
		}

		/**
		 * Hide a specific option in the dropdown
		 * @param {string} optSelector
		 */
		function hideOption( optSelector ) {
			$renameMenu.find( optSelector ).parent().hide();
		}

		return {
			init: init,
			appendTo: appendTo,
			show: show,
			hide: hide,
			getData: getData,
			hideOption: hideOption
		};
	}() );
}() );
