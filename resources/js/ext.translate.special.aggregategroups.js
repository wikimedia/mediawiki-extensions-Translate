/*global alert:false */

( function ( $, mw ) {
	'use strict';

	function getApiParams( $target ) {
		return {
			action: 'aggregategroups',
			aggregategroup: $target.parents( '.mw-tpa-group' ).data( 'groupid' )
		};
	}

	function dissociate( event ) {
		var params,
			$target = $( event.target ),
			api = new mw.Api();

		function successFunction() {
			$target.parent( 'li' ).remove();
		}

		params = $.extend( getApiParams( $target ), {
			'do': 'dissociate',
			group: $target.data( 'groupid' )
		} );

		// Change to csrf when support for MW 1.25 is dropped
		api.postWithToken( 'edit', params )
			.done( successFunction )
			.fail( function ( code, data ) {
				window.alert( data.error && data.error.info );
			} );
	}

	function associate( event, resp ) {
		var successFunction, params, subgroupId,
			$target = $( event.target ),
			$parent = $target.parents( '.mw-tpa-group' ),
			parentId = $parent.data( 'id' ),
			subgroupName = $parent.children( '.tp-group-input' ).val(),
			api = new mw.Api();

		successFunction = function () {
			var aAttr, $a, spanAttr, $span, $ol;

			aAttr = {
				href: mw.util.getUrl( subgroupName ),
				title: subgroupName
			};

			$a = $( '<a>', aAttr ).text( subgroupName );

			spanAttr = {
				'class': 'tp-aggregate-remove-button',
				'data-groupid': subgroupId
			};

			$span = $( '<span>', spanAttr );

			$ol = $( '#mw-tpa-grouplist-' + parentId );
			$ol.append( $( '<li>' ).append( $a, $span ) );
			$span.click( dissociate );
			$parent.children( '.tp-group-input' ).val( '' );
		};

		// Get the label for the value and make API request if valid
		subgroupId = '';
		$.each( resp, function ( key, value ) {
			if ( subgroupName === value.label ) {
				subgroupId = value.id;
			}
		} );

		if ( subgroupId ) {
			params = $.extend( getApiParams( $target ), {
				'do': 'associate',
				group: subgroupId
			} );

			// Change to csrf when support for MW 1.25 is dropped
			api.postWithToken( 'edit', params )
				.done( successFunction )
				.fail( function ( code, data ) {
					window.alert( data.error && data.error.info );
				} );
		} else {
			alert( mw.msg( 'tpt-invalid-group' ) );
		}
	}

	function removeGroup( event ) {
		var params,
			$target = $( event.target ),
			api = new mw.Api();

		function successFunction() {
			$( event.target ).parents( '.mw-tpa-group' ).remove();
		}

		// XXX: 'confirm' is nonstandard.
		if ( $.isFunction( window.confirm ) &&
			window.confirm( mw.msg( 'tpt-aggregategroup-remove-confirm' ) ) ) {
			params = $.extend( getApiParams( $target ), {
				'do': 'remove'
			} );

			// Change to csrf when support for MW 1.25 is dropped
			api.postWithToken( 'edit', params )
				.done( successFunction )
				.fail( function ( code, data ) {
					window.alert( data.error && data.error.info );
				} );
		}
	}

	function editGroup( event ) {
		var $target = $( event.target ),
			$parent = $target.closest( '.mw-tpa-group' ),
			aggregateGroupId =  $parent.data( 'groupid' ),
			$displayGroup = $parent.children( '.tp-display-group' ),
			$editGroup = $parent.children( '.tp-edit-group' ),
			successFunction,
			params,
			aggGroupNameInputName = $editGroup.children( 'input.tp-aggregategroup-edit-name' ),
			aggGroupNameInputDesc = $editGroup.children( 'input.tp-aggregategroup-edit-description' ),
			aggregateGroupName = aggGroupNameInputName.val(),
			aggregateGroupDesc = aggGroupNameInputDesc.val(),
			api = new mw.Api();

		successFunction = function () {
			// Replace the text by the new text without altering the other 2 span tags
			$displayGroup.children( '.tp-name' ).contents().filter( function () {
				return this.nodeType === 3;
			} ).replaceWith( aggregateGroupName );
			$displayGroup.children( '.tp-desc' ).text( aggregateGroupDesc );
			$displayGroup.removeClass( 'hidden' );
			$editGroup.addClass( 'hidden' );
		};

		params = {
			action: 'aggregategroups',
			'do': 'update',
			groupname: aggregateGroupName,
			groupdescription: aggregateGroupDesc,
			aggregategroup: aggregateGroupId,
			format: 'json'
		};

		// Change to csrf when support for MW 1.25 is dropped
		api.postWithToken( 'edit', params )
			.done( successFunction )
			.fail( function ( code, data ) {
				window.alert( data.error.info );
			} );
	}

	function cancelEditGroup( event ) {
		var $parent = $( event.target ).closest( '.mw-tpa-group' );

		$parent.children( '.tp-display-group' ).removeClass( 'hidden' );
		$parent.children( '.tp-edit-group' ).addClass( 'hidden' );
	}

	$( document ).ready( function () {
		var excludeFunction, autocompleteFunction, resp,
			api = new mw.Api(),
			exclude = [],
			groups = [],
			$input = $( '.tp-group-input' );

		excludeFunction = function ( event ) {
			exclude = [];

			if ( groups.length === 0 ) {
				// Get list of subgroups using API
				api.get( {
					action: 'query',
					format: 'json',
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
					groupName = $.trim( groupName );
					exclude.push( groupName );
				}
			);
		};

		autocompleteFunction = function ( request, response ) {
			// Allow case insensitive search
			var inp = new RegExp( request.term, 'i' );

			resp = [];

			$.each( groups, function ( key, value ) {
				if ( value.label.match( inp ) && exclude.indexOf( value.label ) === -1 ) {
					resp.push( value );
				}
			} );
			response( resp );
		};

		$input.focus( excludeFunction );
		$input.autocomplete( {
			source: autocompleteFunction,
			minLength: 0
		} ).focus( function () {
			// Enable showing all groups when nothing is entered
			$( this ).autocomplete( 'search', $( this ).val() );
		} );

		$( '.tp-aggregate-add-button' ).click( function ( event ) {
			associate( event, resp );
		} );
		$( '.tp-aggregate-remove-button' ).click( dissociate );
		$( '.tp-aggregate-remove-ag-button' ).click( removeGroup );
		$( '.tp-aggregategroup-update' ).click( editGroup );
		$( '.tp-aggregategroup-update-cancel' ).click( cancelEditGroup );

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
			var successFunction, params,
				aggGroupNameInputName = $( 'input.tp-aggregategroup-add-name' ),
				aggGroupNameInputDesc = $( 'input.tp-aggregategroup-add-description' ),
				aggregateGroupName = aggGroupNameInputName.val(),
				aggregateGroupDesc = aggGroupNameInputDesc.val(),
				api = new mw.Api();

			// Empty the fields. If they are not emptied, then when another group
			// is added, the values will appear again.
			aggGroupNameInputName.val( '' );
			aggGroupNameInputDesc.val( '' );

			successFunction = function ( data ) {
				var $removeSpan, $editSpan, $displayHeader, $div, $groupSelector, $addButton,
					$cancelButton, $divDisplay, $divEdit, $saveButton,
					aggregateGroupId = data.aggregategroups.aggregategroupId;

				$removeSpan = $( '<span>' ).attr( 'id', aggregateGroupId )
					.addClass( 'tp-aggregate-remove-ag-button' );
				$editSpan = $( '<span>' ).attr( 'id', aggregateGroupId )
					.addClass( 'tp-aggregate-edit-ag-button' );
				// Prints the name and the two spans in a single row
				$displayHeader = $( '<h2>' ).addClass( 'tp-name' ).text( aggregateGroupName )
					.append( $editSpan, $removeSpan );

				$divDisplay = $( '<div>' ).addClass( 'tp-display-group' )
					.append( $displayHeader )
					.append( $( '<p>' ).addClass( 'tp-desc' ).text( aggregateGroupDesc ) );

				$saveButton = ( $( '<input>' )
					.attr( {
						type: 'button',
						'class': 'tp-aggregategroup-update'
					} )
					.val( mw.msg( 'tpt-aggregategroup-update' ) )
					);
				$cancelButton = ( $( '<input>' )
					.attr( {
						type: 'button',
						'class': 'tp-aggregategroup-update-cancel'
					} )
					.val( mw.msg( 'tpt-aggregategroup-update-cancel' ) )
					);
				$divEdit = $( '<div>' )
					.addClass( 'tp-edit-group hidden' )
					.append( $( '<label>' )
						.text( mw.msg( 'tpt-aggregategroup-edit-name' ) ) )
					.append( $( '<input>' )
						.attr( {
							'class': 'tp-aggregategroup-edit-name',
							id: 'tp-agg-name'
						} )
						.val( aggregateGroupName )
					)
					.append( $( '<br /><label>' )
						.text( mw.msg( 'tpt-aggregategroup-edit-description' ) ) )
					.append( $( '<input>' )
						.attr( {
							'class': 'tp-aggregategroup-edit-description',
							id: 'tp-agg-desc'
						} )
						.val( aggregateGroupDesc )
					)
					.append( $saveButton, $cancelButton );

				$div = $( '<div>' ).addClass( 'mw-tpa-group' )
					.append( $divDisplay, $divEdit )
					.append( $( '<ol id=\'mw-tpa-grouplist-' + aggregateGroupId + '\'>' ) );

				$div.data( 'groupid', aggregateGroupId );
				$div.data( 'id', aggregateGroupId );

				$groupSelector = $( '<input>' ).attr( {
					type: 'text',
					'class': 'tp-group-input'
				} );
				$groupSelector.focus( excludeFunction );
				$groupSelector.autocomplete( {
					source: autocompleteFunction,
					minLength: 0
				} ).focus( function () {
					// Enable showing all groups when nothing is entered
					$( this ).autocomplete( 'search', $( this ).val() );
				} );
				$addButton = $( '<input>' )
					.attr( {
						type: 'button',
						'class': 'tp-aggregate-add-button',
						id: aggregateGroupId
					} )
					.val( mw.msg( 'tpt-aggregategroup-add' ) );
				$div.append( $groupSelector, $addButton );
				$addButton.click( function ( event ) {
					associate( event, resp );
				} );
				$editSpan.on( 'click', function ( event ) {
					var $parent = $( event.target ).closest( '.mw-tpa-group' );
					$parent.children( '.tp-display-group' ).addClass( 'hidden' );
					$parent.children( '.tp-edit-group' ).removeClass( 'hidden' );
				} );

				$saveButton.click( editGroup );
				$cancelButton.click( cancelEditGroup );
				$removeSpan.click( removeGroup );
				$( 'div.tpt-add-new-group' ).addClass( 'hidden' );
				$( 'a.tpt-add-new-group' ).before( $div );
			};

			params = {
				action: 'aggregategroups',
				'do': 'add',
				groupname: aggregateGroupName,
				groupdescription: aggregateGroupDesc,
				format: 'json'
			};

			// Change to csrf when support for MW 1.25 is dropped
			api.postWithToken( 'edit', params )
				.done( successFunction )
				.fail( function ( code, data ) {
					window.alert( data.error && data.error.info );
				} );
		} );
	} );
}( jQuery, mediaWiki ) );
