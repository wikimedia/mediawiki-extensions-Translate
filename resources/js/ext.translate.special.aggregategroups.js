( function () {
	'use strict';

	function getApiParams( $target ) {
		return {
			action: 'aggregategroups',
			aggregategroup: $target.parents( '.mw-tpa-group' ).data( 'groupid' )
		};
	}

	function dissociate( event ) {
		var $target = $( event.target ),
			api = new mw.Api();

		function successFunction() {
			$target.parent( 'li' ).remove();
		}

		var params = $.extend( getApiParams( $target ), {
			do: 'dissociate',
			group: $target.data( 'groupid' )
		} );

		api.postWithToken( 'csrf', params )
			.done( successFunction )
			.fail( function ( code, data ) {
				// eslint-disable-next-line no-alert
				alert( data.error && data.error.info );
			} );
	}

	function associate( event, resp ) {
		var $target = $( event.target ),
			$parent = $target.parents( '.mw-tpa-group' ),
			parentId = $parent.data( 'id' ),
			subgroupName = $parent.children( '.tp-group-input' ).val(),
			api = new mw.Api();

		var subgroupId;
		var successFunction = function () {
			var aAttr, $a, spanAttr, $span, $ol;

			aAttr = {
				href: mw.util.getUrl( subgroupName ),
				title: subgroupName
			};

			$a = $( '<a>', aAttr ).text( subgroupName );

			spanAttr = {
				class: 'tp-aggregate-remove-button',
				'data-groupid': subgroupId
			};

			$span = $( '<span>', spanAttr );

			$ol = $( '#mw-tpa-grouplist-' + parentId );
			$ol.append( $( '<li>' ).append( $a, $span ) );
			$span.on( 'click', dissociate );
			$parent.children( '.tp-group-input' ).val( '' );
		};

		// Get the label for the value and make API request if valid
		subgroupId = '';
		for ( var i = 0; i < resp.length; ++i ) {
			if ( subgroupName === resp[ i ].label ) {
				subgroupId = resp[ i ].id;
				break;
			}
		}

		if ( subgroupId ) {
			var params = $.extend( getApiParams( $target ), {
				do: 'associate',
				group: subgroupId
			} );

			api.postWithToken( 'csrf', params )
				.done( successFunction )
				.fail( function ( code, data ) {
					// eslint-disable-next-line no-alert
					alert( data.error && data.error.info );
				} );
		} else {
			// eslint-disable-next-line no-alert
			alert( mw.msg( 'tpt-invalid-group' ) );
		}
	}

	function removeGroup( event ) {
		var $target = $( event.target ),
			api = new mw.Api();

		function successFunction() {
			$( event.target ).parents( '.mw-tpa-group' ).remove();
		}

		// XXX: 'confirm' is nonstandard.
		if ( typeof window.confirm === 'function' &&
			// eslint-disable-next-line no-alert
			window.confirm( mw.msg( 'tpt-aggregategroup-remove-confirm' ) ) ) {
			var params = $.extend( getApiParams( $target ), {
				do: 'remove'
			} );

			api.postWithToken( 'csrf', params )
				.done( successFunction )
				.fail( function ( code, data ) {
					// eslint-disable-next-line no-alert
					alert( data.error && data.error.info );
				} );
		}
	}

	function editGroup( event ) {
		var $target = $( event.target ),
			$parent = $target.closest( '.mw-tpa-group' ),
			aggregateGroupId = $parent.data( 'groupid' ),
			$displayGroup = $parent.children( '.tp-display-group' ),
			$editGroup = $parent.children( '.tp-edit-group' ),
			$aggGroupNameInputName = $editGroup.children( 'input.tp-aggregategroup-edit-name' ),
			$aggGroupNameInputDesc = $editGroup.children( 'input.tp-aggregategroup-edit-description' ),
			aggregateGroupName = $aggGroupNameInputName.val(),
			aggregateGroupDesc = $aggGroupNameInputDesc.val(),
			api = new mw.Api();

		var successFunction = function () {
			// Replace the text by the new text without altering the other 2 span tags
			$displayGroup.children( '.tp-name' ).contents().filter( function () {
				return this.nodeType === 3;
			} ).replaceWith( aggregateGroupName );
			$displayGroup.children( '.tp-desc' ).text( aggregateGroupDesc );
			$displayGroup.removeClass( 'hidden' );
			$editGroup.addClass( 'hidden' );
		};

		var params = {
			action: 'aggregategroups',
			do: 'update',
			groupname: aggregateGroupName,
			groupdescription: aggregateGroupDesc,
			aggregategroup: aggregateGroupId
		};

		api.postWithToken( 'csrf', params )
			.done( successFunction )
			.fail( function ( code, data ) {
				// eslint-disable-next-line no-alert
				alert( data.error.info );
			} );
	}

	function cancelEditGroup( event ) {
		var $parent = $( event.target ).closest( '.mw-tpa-group' );

		$parent.children( '.tp-display-group' ).removeClass( 'hidden' );
		$parent.children( '.tp-edit-group' ).addClass( 'hidden' );
	}

	$( function () {
		var api = new mw.Api(),
			exclude = [],
			groups = [],
			$input = $( '.tp-group-input' );

		var excludeFunction = function ( event ) {
			exclude = [];

			if ( groups.length === 0 ) {
				// Get list of subgroups using API
				api.get( {
					action: 'query',
					meta: 'messagegroups',
					mgformat: 'tree',
					mgroot: 'all',
					mgprop: 'label|id'
				} ).done( function ( result ) {
					groups = result.query.messagegroups;
				} );
			}

			// Exclude groups already present
			$( event.target ).closest( '.mw-tpa-group' ).find( 'li' ).each(
				function ( key, data ) {
					// Need to trim to remove the trailing whitespace
					// Can't use innerText not supported by Firefox
					var groupName = $( data ).text();
					groupName = groupName.trim();
					exclude.push( groupName );
				}
			);
		};

		var resp;
		var autocompleteFunction = function ( request, response ) {
			// Allow case insensitive search
			var inp = new RegExp( request.term, 'i' );

			resp = [];

			Object.keys( groups ).forEach( function ( key ) {
				if (
					groups[ key ].label.match( inp ) &&
					exclude.indexOf( groups[ key ].label ) === -1
				) {
					resp.push( groups[ key ] );
				}
			} );

			response( resp );
		};

		$input.on( 'focus', excludeFunction );
		$input.autocomplete( {
			source: autocompleteFunction,
			minLength: 0
		} ).focus( function () {
			// Enable showing all groups when nothing is entered
			$( this ).autocomplete( 'search', $( this ).val() );
		} );

		$( '.tp-aggregate-add-button' ).on( 'click', function ( event ) {
			associate( event, resp );
		} );
		$( '.tp-aggregate-remove-button' ).on( 'click', dissociate );
		$( '.tp-aggregate-remove-ag-button' ).on( 'click', removeGroup );
		$( '.tp-aggregategroup-update' ).on( 'click', editGroup );
		$( '.tp-aggregategroup-update-cancel' ).on( 'click', cancelEditGroup );

		$( 'a.tpt-add-new-group' ).on( 'click', function ( event ) {
			$( 'div.tpt-add-new-group' ).removeClass( 'hidden' );
			// Link has anchor which goes top of the page
			event.preventDefault();
		} );

		$( '.tp-aggregate-edit-ag-button' ).on( 'click', function ( event ) {
			var $parent = $( event.target ).closest( '.mw-tpa-group' );

			$parent.children( '.tp-display-group' ).addClass( 'hidden' );
			$parent.children( '.tp-edit-group' ).removeClass( 'hidden' );
		} );

		$( '#tpt-aggregategroups-save' ).on( 'click', function () {
			var $aggGroupNameInputName = $( 'input.tp-aggregategroup-add-name' ),
				$aggGroupNameInputDesc = $( 'input.tp-aggregategroup-add-description' ),
				aggregateGroupName = $aggGroupNameInputName.val(),
				aggregateGroupDesc = $aggGroupNameInputDesc.val();

			// Empty the fields. If they are not emptied, then when another group
			// is added, the values will appear again.
			$aggGroupNameInputName.val( '' );
			$aggGroupNameInputDesc.val( '' );

			var successFunction = function ( data ) {
				var aggregateGroupId = data.aggregategroups.aggregategroupId;

				var $removeSpan = $( '<span>' ).attr( 'id', aggregateGroupId )
					.addClass( 'tp-aggregate-remove-ag-button' );
				var $editSpan = $( '<span>' ).attr( 'id', aggregateGroupId )
					.addClass( 'tp-aggregate-edit-ag-button' );
				// Prints the name and the two spans in a single row
				var $displayHeader = $( '<h2>' ).addClass( 'tp-name' ).text( aggregateGroupName )
					.append( $editSpan, $removeSpan );

				var $divDisplay = $( '<div>' ).addClass( 'tp-display-group' )
					.append( $displayHeader )
					.append( $( '<p>' ).addClass( 'tp-desc' ).text( aggregateGroupDesc ) );

				var $saveButton = $( '<input>' )
					.attr( {
						type: 'button',
						class: 'tp-aggregategroup-update'
					} )
					.val( mw.msg( 'tpt-aggregategroup-update' ) );
				var $cancelButton = $( '<input>' )
					.attr( {
						type: 'button',
						class: 'tp-aggregategroup-update-cancel'
					} )
					.val( mw.msg( 'tpt-aggregategroup-update-cancel' ) );
				var $divEdit = $( '<div>' )
					.addClass( 'tp-edit-group hidden' )
					.append( $( '<label>' )
						.text( mw.msg( 'tpt-aggregategroup-edit-name' ) ) )
					.append( $( '<input>' )
						.attr( {
							class: 'tp-aggregategroup-edit-name',
							id: 'tp-agg-name'
						} )
						.val( aggregateGroupName )
					)
					.append(
						$( '<br>' ),
						$( '<label>' )
							.text( mw.msg( 'tpt-aggregategroup-edit-description' ) )
					)
					.append( $( '<input>' )
						.attr( {
							class: 'tp-aggregategroup-edit-description',
							id: 'tp-agg-desc'
						} )
						.val( aggregateGroupDesc )
					)
					.append( $saveButton, $cancelButton );

				var $div = $( '<div>' ).addClass( 'mw-tpa-group' )
					.append( $divDisplay, $divEdit )
					.append( $( '<ol id=\'mw-tpa-grouplist-' + aggregateGroupId + '\'>' ) );

				$div.data( 'groupid', aggregateGroupId );
				$div.data( 'id', aggregateGroupId );

				var $groupSelector = $( '<input>' ).attr( {
					type: 'text',
					class: 'tp-group-input'
				} );
				$groupSelector.on( 'focus', excludeFunction );
				$groupSelector.autocomplete( {
					source: autocompleteFunction,
					minLength: 0
				} ).focus( function () {
					// Enable showing all groups when nothing is entered
					$( this ).autocomplete( 'search', $( this ).val() );
				} );
				var $addButton = $( '<input>' )
					.attr( {
						type: 'button',
						class: 'tp-aggregate-add-button',
						id: aggregateGroupId
					} )
					.val( mw.msg( 'tpt-aggregategroup-add' ) );
				$div.append( $groupSelector, $addButton );
				$addButton.on( 'click', function ( event ) {
					associate( event, resp );
				} );
				$editSpan.on( 'click', function ( event ) {
					var $parent = $( event.target ).closest( '.mw-tpa-group' );
					$parent.children( '.tp-display-group' ).addClass( 'hidden' );
					$parent.children( '.tp-edit-group' ).removeClass( 'hidden' );
				} );

				$saveButton.on( 'click', editGroup );
				$cancelButton.on( 'click', cancelEditGroup );
				$removeSpan.on( 'click', removeGroup );
				$( 'div.tpt-add-new-group' ).addClass( 'hidden' );
				$( 'a.tpt-add-new-group' ).before( $div );
			};

			var params = {
				action: 'aggregategroups',
				do: 'add',
				groupname: aggregateGroupName,
				groupdescription: aggregateGroupDesc
			};

			api.postWithToken( 'csrf', params )
				.done( successFunction )
				.fail( function ( code, data ) {
					// eslint-disable-next-line no-alert
					alert( data.error && data.error.info );
				} );
		} );
	} );
}() );
