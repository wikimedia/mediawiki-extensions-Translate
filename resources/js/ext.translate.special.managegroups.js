( function () {
	var RenameDropdown,
		GroupSynchronization;

	$( function () {
		RenameDropdown.init();
		GroupSynchronization.init();

		// Create and append a window manager.
		var windowManager = new OO.ui.WindowManager();
		windowManager.$element.appendTo( document.body );

		// Create a new process dialog window.
		var renameDialog = new mw.translate.MessageRenameDialog( {
			classes: [ 'smg-rename-dialog' ],
			size: 'large'
		}, function ( renameParams ) {
			return setRename( renameParams ).done( function ( data ) {
				if ( data.managemessagegroups && data.managemessagegroups.success ) {
					location.reload();
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

		// Add the window to window manager using the addWindows() method.
		windowManager.addWindows( [ renameDialog ] );

		/**
		 * Attach the click handler to display the rename dropdown.
		 */
		$( '#mw-content-text' ).on( 'click', '.smg-rename-actions', function ( event ) {
			var $target = $( event.target );
			var $parentContainer = $target.parents( '.mw-translate-smg-change' );
			RenameDropdown.appendTo( event.target, $parentContainer, {
				groupId: $target.data( 'groupId' ),
				msgKey: $target.data( 'msgkey' ),
				msgTitle: $target.data( 'msgtitle' )
			} ).show();

			if ( $parentContainer.hasClass( 'smg-change-addition' ) ) {
				// For a new message, the "add as new" option is hidden.
				RenameDropdown.hideOption( '.smg-rename-new-action' );
			}
			event.preventDefault();
		} );

		$( document.documentElement ).on( 'click', function ( event ) {
			if ( !event.isDefaultPrevented() ) {
				RenameDropdown.hide();
			}
		} );

		/**
		 * Click handler triggered when "Add as rename" is clicked in the dropdown.
		 */
		$( '.smg-rename-rename-action' ).on( 'click', function () {
			var keyData = RenameDropdown.getData(),
				$renameButton = getRenameButton( $( event.target ) );
			toggleLoading( $renameButton, true );

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
				} ).always( function () {
					toggleLoading( $renameButton, false );
				} );
		} );

		/**
		 * Click handler triggered when "Add as new" is clicked in the dropdown.
		 */
		$( '.smg-rename-new-action' ).on( 'click', function () {
			var keyData = RenameDropdown.getData(),
				$renameButton = getRenameButton( $( event.target ) ),
				isReloading = false;
			toggleLoading( $renameButton, true );

			setAsNew( keyData.groupId, keyData.msgKey ).done( function ( data ) {
				if ( data.managemessagegroups && data.managemessagegroups.success ) {
					location.reload();
					isReloading = true;
				}
			} ).fail( function ( code, result ) {
				if ( result.error ) {
					mw.notify( result.error.info, {
						type: 'error',
						tag: 'new-error'
					} );
				}
			} ).always( function () {
				// If page is reloading, don't bother hiding the loader.
				if ( isReloading === false ) {
					toggleLoading( $renameButton, false );
				}
			} );
		} );
	} );

	function getRenameButton( $target ) {
		return $target.parents( '.mw-translate-smg-change' ).find( '.smg-rename-actions' );
	}

	function toggleLoading( $element, isLoading ) {
		if ( isLoading ) {
			// hide all the rename buttons, but show the current one with loading animation
			$( '.smg-rename-actions' ).addClass( 'mw-translate-hide' );
			$element.removeClass( 'mw-translate-hide' ).addClass( 'loading' );
		} else {
			$( '.smg-rename-actions' ).removeClass( 'mw-translate-hide' );
			$element.removeClass( 'loading' );
		}
	}

	/**
	 * Fetch the possible renames for a given message.
	 *
	 * @param {string} groupId
	 * @param {string} msgKey
	 * @return {jQuery.Promise}
	 */
	function getRenames( groupId, msgKey ) {
		var api = new mw.Api();
		var params = {
			action: 'query',
			meta: 'managemessagegroups',
			formatversion: 2,
			mmggroupId: groupId,
			mmgmessageKey: msgKey
		};

		var changesetName = getChangesetName();
		if ( changesetName !== null ) {
			params.mmgchangesetName = changesetName;
		}

		return api.get( params ).then( function ( result ) {
			return result.query;
		} );
	}

	/**
	 * Identifies and returns the group name from the URL
	 *
	 * @return {string}
	 */
	function getChangesetName() {
		var locationPaths = window.location.pathname.split( '/' );
		var suffix = locationPaths.pop();
		var pageTitle = $( '#smgPageTitle' ).val();

		if ( suffix && suffix.indexOf( pageTitle ) === -1 ) {
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
	 *
	 * @param {Object} renameParams
	 * @param {string} renameParams.groupId
	 * @param {string} renameParams.selectedKey Key to be matched to. This message will be renamed.
	 * @param {string} renameParams.targetKey Key from the source
	 * @return {jQuery.Promise}
	 */
	function setRename( renameParams ) {
		var api = new mw.Api();

		var params = {
			action: 'managemessagegroups',
			groupId: renameParams.groupId,
			renameMessageKey: renameParams.selectedKey,
			messageKey: renameParams.targetKey,
			operation: 'rename',
			changesetModified: getChangesetModifiedTime(),
			assert: 'user',
			formatversion: 2
		};

		var changesetName = getChangesetName();
		if ( changesetName !== null ) {
			params.changesetName = changesetName;
		}
		return api.postWithToken( 'csrf', params );
	}

	/**
	 * Mark the message as a new message
	 *
	 * @param {string} groupId
	 * @param {string} msgKey
	 * @return {jQuery.Promise}
	 */
	function setAsNew( groupId, msgKey ) {
		var api = new mw.Api();

		var params = {
			action: 'managemessagegroups',
			groupId: groupId,
			messageKey: msgKey,
			operation: 'new',
			changesetModified: getChangesetModifiedTime(),
			assert: 'user',
			formatversion: 2
		};

		var changesetName = getChangesetName();
		if ( changesetName !== null ) {
			params.changesetName = changesetName;
		}

		return api.postWithToken( 'csrf', params );
	}

	RenameDropdown = ( function () {
		var $renameMenu;

		/**
		 * Initialization function. Creates the elements for the rename dropdown
		 *
		 * @chainable
		 */
		function init() {
			$renameMenu = getRenameDropdown().appendTo( document.body );
			return this;
		}

		/**
		 * Returns the HTML element for the dropdown
		 *
		 * @return {jQuery}
		 */
		function getRenameDropdown() {
			var $addAsRename = $( '<li>' ).append(
					$( '<button>' )
						.attr( 'type', 'button' )
						.addClass( 'smg-rename-new-action mw-translate-hide' )
						.text( mw.msg( 'translate-smg-rename-new' ) )
				),
				$addAsNew = $( '<li>' ).append(
					$( '<button>' )
						.attr( 'type', 'button' )
						.addClass( 'smg-rename-rename-action mw-translate-hide' )
						.text( mw.msg( 'translate-smg-rename-rename' ) )
				);

			return $( '<ul>' ).addClass( 'smg-rename-dropdown-menu' ).append(
				$addAsRename,
				$addAsNew
			);
		}

		/**
		 * Displays the rename menu
		 *
		 * @chainable
		 */
		function show() {
			$renameMenu.addClass( 'show' );
			return this;
		}

		/**
		 * Hides the rename menu
		 *
		 * @chainable
		 */
		function hide() {
			$renameMenu.removeClass( 'show' );
			return this;
		}

		/**
		 * Appends the dropdown to a container element
		 *
		 * @param {jQuery} target Target trigger element
		 * @param {jQuery} $container Container to which to append the menu
		 * @param {Object} customData Custom data to be associated with the menu
		 * @chainable
		 */
		function appendTo( target, $container, customData ) {
			var $currentTarget = $( target );
			$container.append( $renameMenu );
			$renameMenu.css( {
				top: $currentTarget.position().top + $currentTarget.height(),
				left: $currentTarget.position().left - $renameMenu.width() + $currentTarget.width()
			} ).data( 'custom-data', customData );

			// When appending, show all the li's by default since based on the
			// message type (RENAME / NEW) some li's may be hidden previously
			$renameMenu.find( 'li' ).removeClass( 'mw-translate-hide' );
			return this;
		}

		/**
		 * Fetch the custom data associated with rename menu
		 *
		 * @return {Object}
		 */
		function getData() {
			return $renameMenu.data( 'custom-data' );
		}

		/**
		 * Hide a specific option in the dropdown
		 *
		 * @param {string} optSelector
		 * @chainable
		 */
		function hideOption( optSelector ) {
			$renameMenu.find( optSelector ).parent().addClass( 'mw-translate-hide' );
			return this;
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

	GroupSynchronization = ( function () {
		function init() {
			$( '.js-group-sync-message-resolve' ).on( 'click', markMessageAsResolved );
			$( '.js-group-sync-group-resolve' ).on( 'click', markGroupAsResolved );
		}

		function markMessageAsResolved() {
			var $target = $( this ),
				groupId = $target.data( 'groupId' ),
				messageTitle = $target.data( 'msgTitle' );

			showLoading( $target );

			markAsResolved( 'resolveMessage', groupId, messageTitle ).done( function ( response ) {
				var responseData = response.managegroupsynchronizationcache || null;
				if ( responseData && responseData.success ) {
					if ( responseData.data.groupRemainingMessageCount === 0 ) {
						removeParentGroupBlock( $target );
					} else {
						// Remove the message from the DOM
						$target.parents( '.js-group-sync-message-error' ).remove();
					}
				}
			} ).fail( function ( code, result ) {
				handleResolutionFailure( code, result, groupId, messageTitle );
			} ).always( function () {
				hideLoading( $target );
			} );
		}

		function markGroupAsResolved() {
			var $target = $( this ),
				groupId = $target.data( 'groupId' );

			showLoading( $target );

			markAsResolved( 'resolveGroup', groupId ).done( function ( response ) {
				var responseData = response.managegroupsynchronizationcache || null;
				if ( responseData && responseData.success ) {
					removeParentGroupBlock( $target );
				}
			} ).fail( function ( code, result ) {
				handleResolutionFailure( code, result, groupId );
			} ).always( function () {
				hideLoading( $target );
			} );
		}

		function markAsResolved( operation, groupId, messageTitle ) {
			var api = new mw.Api();

			var params = {
				action: 'managegroupsynchronizationcache',
				group: groupId,
				operation: operation,
				assert: 'user',
				formatversion: 2
			};

			if ( messageTitle ) {
				params.title = messageTitle;
			}

			return api.postWithToken( 'csrf', params );
		}

		function removeParentGroupBlock( $child ) {
			// Remove the entire group block from DOM
			$child.parents( '.js-group-sync-group-errors' ).remove();
			// If all groups are resolved, remove the group sync error block
			if ( !$( '.js-group-sync-group-errors' ).length ) {
				$( '.js-group-sync-groups-with-error' ).remove();
			}
		}

		function handleResolutionFailure( code, result, groupId, messageTitle ) {
			var errorInfo = result && result.error ? result.error.info : null;
			if ( errorInfo ) {
				mw.notify( result.error.info, {
					type: 'error',
					tag: 'new-error'
				} );
			} else {
				// Unknown error
				mw.notify( mw.msg( 'translate-smg-unknown-error' ), {
					type: 'error',
					tag: 'new-error'
				} );
			}

			mw.log.error( 'Error while resolving group or message. Param: ' + JSON.stringify( {
				groupId: groupId,
				messageTitle: messageTitle,
				errorCode: code,
				errorInfo: errorInfo
			} ) );
		}

		function showLoading( $target ) {
			$target.addClass( 'loading' )
				.text( mw.msg( 'translate-smg-loading' ) )
				.removeAttr( 'href' );
		}

		function hideLoading( $target ) {
			$target.removeClass( 'loading' )
				.text( mw.msg( 'translate-smg-group-action-resolve' ) )
				.prop( 'href', '#' );
		}

		return {
			init: init
		};
	}() );
}() );
