( function ( $, mw ) {
	'use strict';

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
			$selected = $( '#mw-tpa-groupselect-' + parentId + ' option:selected' ),
			subgroupId = $selected.val(),
			subgroupName = $selected.text();

		successFunction = function ( data ) {
			if ( data.error ) {
				window.alert( data.error.info );
			} else {
				var aAttr, $a, spanAttr, $span, $ol;

				aAttr = {
					href: mw.util.wikiGetlink( subgroupName ),
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

	$( document ).ready( function () {
		$( '.tp-aggregate-add-button' ).click( associate );
		$( '.tp-aggregate-remove-button' ).click( dissociate );
		$( '.tp-aggregate-remove-ag-button' ).click( removeGroup );

		$( 'a.tpt-add-new-group' ).on( 'click', function ( event ) {
			$( 'div.tpt-add-new-group' ).removeClass( 'hidden' );
			// Link has anchor which goes top of the page
			event.preventDefault();
		} );

		// FIXME: These selects should be populated with AJAX.
		// At least there is no point in outputting them in HTML
		// for each group. One would be enough that could be cloned.
		$( '.mw-tpa-groupselect' ).eachAsync( {
			loop: function () {
				/*jshint camelcase:false*/
				$(this).chosen( {
					search_contains: true
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
					var $removeSpan, $div, $groupSelector, $addButton,
						aggregateGroupId = data.aggregategroups.aggregategroupId;

					$removeSpan = $( '<span>' ).attr( 'id', aggregateGroupId ).addClass( 'tp-aggregate-remove-ag-button' );
					$div = $( '<div class=\'mw-tpa-group\'>' )
						.append( $( '<h2>' ).text( aggregateGroupName ).append( $removeSpan ) )
						.append( $( '<p>' ).text( aggregateGroupDesc ) )
						.append( $( '<ol id=\'mw-tpa-grouplist-' + aggregateGroupId + '\'>' ) );

					$div.data( 'groupid', aggregateGroupId );
					$div.data( 'id', aggregateGroupId );

					if ( $select.length > 0 ) {
						$groupSelector = $( '<select>' ).attr({
							'id': 'mw-tpa-groupselect-' + aggregateGroupId,
							'class': 'mw-tpa-groupselect'
						});

						$.each( data.aggregategroups.groups, function ( key, value ) {
							$groupSelector.append( $( '<option>', { value: key } ).text( value ) );
						} );

						$addButton = $( $( 'input.tp-aggregate-add-button' )[0] ).clone();
						$addButton.attr( 'id', aggregateGroupId );
						$div.append( $groupSelector, $addButton );
						$addButton.click( associate );
						$removeSpan.click( removeGroup );
						$( 'div.tpt-add-new-group' ).addClass( 'hidden' );

						setTimeout( function () {
							$groupSelector.chosen( {
								/*jshint camelcase:false*/
								search_contains: true
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
