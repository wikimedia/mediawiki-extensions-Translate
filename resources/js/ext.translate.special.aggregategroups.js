( function ( $, mw ) {
	'use strict';

	// BC for MW <= 1.21
	var getUrl = mw.util.getUrl || mw.util.wikiGetlink;

	function getApiParams( $target ) {
		return {
			action: 'aggregategroups',
			token: $( '#token' ).val(),
			aggregategroup: $target.parents( '.mw-tpa-group' ).data( 'groupid' ),
			format: 'json'
		};
	}

	function dissociate( event ) {
		var params,
			$target = $( event.target ),
			parentId = $target.parents( '.mw-tpa-group' ).data( 'id' ),
			$select = $( '#mw-tpa-groupselect-' + parentId );

		function successFunction( data ) {
			if ( data.error ) {
				window.alert( data.error.info );
			} else {
				$( '<option>', { value: $target.data( 'groupid' ) } )
					.text( $target.siblings( 'a' ).text() )
					.appendTo( $select );
				$target.parent( 'li' ).remove();
				$select.trigger( 'liszt:updated' );
			}
		}

		params = $.extend( getApiParams( $target ), {
			'do': 'dissociate',
			group: $target.data( 'groupid' )
		} );
		$.post( mw.util.wikiScript( 'api' ), params, successFunction );
	}

	function associate( event ) {
		var successFunction, params,
			$target = $( event.target ),
			parentId = $target.parents( '.mw-tpa-group' ).data( 'id' ),
			$select = $( '#mw-tpa-groupselect-' + parentId ),
			$selected = $select.find( 'option:selected' ),
			subgroupId = $selected.val(),
			subgroupName = $selected.text();

		successFunction = function ( data ) {
			if ( data.error ) {
				window.alert( data.error.info );
			} else {
				var aAttr, $a, spanAttr, $span, $ol;

				aAttr = {
					href: getUrl( subgroupName ),
					title: subgroupName
				};

				$a = $( '<a>', aAttr ).text( subgroupName );

				spanAttr = {
					'class': 'tp-aggregate-remove-button',
					'data-groupid': subgroupId
				};

				$span = $( '<span>', spanAttr );

				$ol = $( '#mw-tpa-grouplist-' + parentId );
				$ol.append( $( '<li>' ).append( $a.after( $span ) ) );

				// remove this group from the select.
				$selected.remove();
				$select.trigger( 'liszt:updated' );
				$span.click( dissociate );
			}
		};

		params = $.extend( getApiParams( $target ), {
			'do': 'associate',
			group: subgroupId
		} );
		$.post( mw.util.wikiScript( 'api' ), params, successFunction );
	}

	function removeGroup( event ) {
		var params, $target = $( event.target );

		function successFunction( data ) {
			if ( data.error ) {
				window.alert( data.error.info );
			} else {
				$( event.target ).parents( '.mw-tpa-group' ).remove();
			}
		}

		// XXX: 'confirm' is nonstandard.
		if ( $.isFunction( window.confirm ) &&
			window.confirm( mw.msg( 'tpt-aggregategroup-remove-confirm' ) ) ) {
			params = $.extend( getApiParams( $target ), {
				'do': 'remove'
			} );
			$.post( mw.util.wikiScript( 'api' ), params, successFunction );
		}
	}

	function editGroup( event ) {
		var $target = $( event.target );
		var $parent = $target.closest( '.mw-tpa-group' );
		var aggregateGroupId =  $parent.data( 'groupid' ),
			$displayGroup = $parent.children( '.tp-display-group' ),
			$editGroup = $parent.children( '.tp-edit-group' );
		var successFunction, errorFunction, params,
			aggGroupNameInputName = $editGroup.children( 'input.tp-aggregategroup-edit-name' ),
			aggGroupNameInputDesc = $editGroup.children( 'input.tp-aggregategroup-edit-description' ),
			aggregateGroupName = aggGroupNameInputName.val(),
			aggregateGroupDesc = aggGroupNameInputDesc.val();

		successFunction = function () {
			// Replace the text by the new text without altering the other 2 span tags
			$displayGroup.children( '.tp-name' ).contents().filter(function() {
				return this.nodeType === 3;
			}).replaceWith( aggregateGroupName );
			$displayGroup.children( '.tp-desc' ).text( aggregateGroupDesc );
			$displayGroup.removeClass( 'hidden' );
			$editGroup.addClass( 'hidden' );
		};
		errorFunction = function ( data ) {
			window.alert( data );
		};

		params = {
			action: 'aggregategroups',
			'do': 'update',
			token: $( '#token' ).val(),
			groupname: aggregateGroupName,
			groupdescription: aggregateGroupDesc,
			aggregategroup: aggregateGroupId,
			format: 'json'
		};
		var api = new mw.Api();
		api.post( params ).done( successFunction ).fail( errorFunction );
	 }

	function cancelEditGroup( event ) {
		var $parent = $( event.target ).closest( '.mw-tpa-group' );

		$parent.children( '.tp-display-group' ).removeClass( 'hidden' );
		$parent.children( '.tp-edit-group' ).addClass( 'hidden' );
	}

	$( document ).ready( function () {
		$( '.tp-aggregate-add-button' ).click( associate );
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

		// FIXME: These selects should be populated with AJAX.
		// At least there is no point in outputting them in HTML
		// for each group. One would be enough that could be cloned.
		$( '.mw-tpa-groupselect' ).eachAsync( {
			loop: function () {
				$(this).chosen( {
					'search_contains': true
				} );
			}
		} );

		$( '#tpt-aggregategroups-save' ).on( 'click', function () {
			var $select, successFunction, params,
				aggGroupNameInputName = $( 'input.tp-aggregategroup-add-name' ),
				aggGroupNameInputDesc = $( 'input.tp-aggregategroup-add-description' ),
				aggregateGroupName = aggGroupNameInputName.val(),
				aggregateGroupDesc = aggGroupNameInputDesc.val();

			// Empty the fields. If they are not emptied, then when another group
			// is added, the values will appear again. Bug 36296.
			aggGroupNameInputName.val( '' );
			aggGroupNameInputDesc.val( '' );

			$select = $( 'div.mw-tpa-group select' );

			successFunction = function ( data ) {
				if ( data.error ) {
					window.alert( data.error.info );
				} else {
					var $removeSpan, $editSpan, $div, $groupSelector, $addButton,
						$cancelButton, $divDisplay, $divEdit, $saveButton,
						aggregateGroupId = data.aggregategroups.aggregategroupId;

					$removeSpan = $( '<span>' ).attr( 'id', aggregateGroupId )
						.addClass( 'tp-aggregate-remove-ag-button' );
					$editSpan = $( '<span>' ).attr( 'id', aggregateGroupId )
						.addClass( 'tp-aggregate-edit-ag-button' );

					$divDisplay = $( '<div>' ).addClass( 'tp-display-group' )
						.append( $( '<h2>' ).addClass( 'tp-name' ).text( aggregateGroupName ) )
						// TODO Appears on a new line- Need to fix
						.append( $editSpan, $removeSpan )
						.append( $( '<p>' ).addClass( 'tp-desc' ).text( aggregateGroupDesc ) );

					$saveButton = ( $( '<input>' )
						.attr({
							type: 'button',
							class: 'tp-aggregategroup-update',
							value:  mw.msg( 'tpt-aggregategroup-update' )
						})
					);
					$cancelButton = ( $( '<input>' )
						.attr({
							type: 'button',
							class: 'tp-aggregategroup-update-cancel',
							value: mw.msg( 'tpt-aggregategroup-update-cancel' )
						})
					);
					$divEdit = $( '<div>' )
						.addClass( 'tp-edit-group hidden' )
						.append( $( '<label>' )
							.text( mw.msg( 'tpt-aggregategroup-edit-name' ) ) )
					    .append( $( '<input>' )
							.attr({
								class: 'tp-aggregategroup-edit-name',
								id: 'tp-agg-name',
								value: aggregateGroupName
							})
						)
						.append( $( '<br /><label>' )
							.text( mw.msg( 'tpt-aggregategroup-edit-description' ) ) )
						.append( $( '<input>' )
							.attr({
								class: 'tp-aggregategroup-edit-description',
								id: 'tp-agg-desc',
								value: aggregateGroupDesc
							})
						)
						.append( $saveButton, $cancelButton );

					$div = $( '<div>' ).addClass( 'mw-tpa-group' )
						.append( $divDisplay )
						.append( $divEdit )
						.append( $( '<ol id=\'mw-tpa-grouplist-' + aggregateGroupId + '\'>' ) );

					$div.data( 'groupid', aggregateGroupId );
					$div.data( 'id', aggregateGroupId );

					if ( $select.length > 0 ) {
						$groupSelector = $( '<select>' ).attr( {
							'id': 'mw-tpa-groupselect-' + aggregateGroupId,
							'class': 'mw-tpa-groupselect'
						} );

						$.each( data.aggregategroups.groups, function ( key, value ) {
							$groupSelector.append( $( '<option>', { value: key } ).text( value ) );
						} );

						$addButton = $( $( 'input.tp-aggregate-add-button' )[0] ).clone();
						$addButton.attr( 'id', aggregateGroupId );
						$div.append( $groupSelector, $addButton );
						$addButton.click( associate );
						$editSpan.on( 'click', function ( event ) {
							$( event.target ).parents( '.mw-tpa-group' )
								.children( '.tp-display-group' ).addClass( 'hidden' );
							$( event.target ).parents( '.mw-tpa-group' )
								.children( '.tp-edit-group' ).removeClass( 'hidden' );
						} );

						$saveButton.click( editGroup );
						$cancelButton.click( cancelEditGroup );
						$removeSpan.click( removeGroup );
						$( 'div.tpt-add-new-group' ).addClass( 'hidden' );

						setTimeout( function () {
							$groupSelector.chosen( {
								'search_contains': true
							} );
						}, 1 );
					} else {
						// First group in the wiki. Cannot clone the group selector, just reload this time.
						location.reload();
					}
					$( 'a.tpt-add-new-group' ).before( $div );
				}
			};
			params = {
				action: 'aggregategroups',
				'do': 'add',
				token: $( '#token' ).val(),
				groupname: aggregateGroupName,
				groupdescription: aggregateGroupDesc,
				format: 'json'
			};
			$.post( mw.util.wikiScript( 'api' ), params, successFunction );
		} );
	} );
} ( jQuery, mediaWiki ) );
