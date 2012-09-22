( function ( $, mw ) {
	"use strict";

	function getApiParams( $target ) {
		return {
			action: 'aggregategroups',
			token: $( '#token' ).val(),
			aggregategroup: $target.parents( '.mw-tpa-group' ).data( 'groupid' ),
			format: "json"
		};
	}

	function dissociate( event ) {
		var	$target = $( event.target ),
			parentId = $target.parents( '.mw-tpa-group' ).data( 'id' ),
			$select = $( '#mw-tpa-groupselect-' + parentId );

		function successFunction( data, textStatus ) {
			if ( data.error ) {
				alert( data.error.info );
			}  else {
				$( '<option>', { value: $target.data( 'groupid' ) } )
					.text( $target.siblings( 'a' ).text() )
					.appendTo( $select );
				$target.parent( 'li' ).remove();
			}
		}

		var params = $.extend( getApiParams( $target ), {
			'do' : 'dissociate',
			group: $target.data( 'groupid' )
		} );
		$.post( mw.util.wikiScript( 'api' ), params, successFunction );
	}

	function associate( event ) {
		var	$target = $( event.target ),
			parentId = $target.parents( '.mw-tpa-group' ).data( 'id' ),
			$selected = $( '#mw-tpa-groupselect-' + parentId + ' option:selected' ),
			subgroupId = $selected.val(),
			subgroupName = $selected.text();

		var successFunction = function( data, textStatus ) {
			if ( data.error ) {
				alert( data.error.info );
			} else {
				var aAttr = {
					href: mw.util.wikiGetlink( subgroupName ),
					title: subgroupName
				};
				var $a = $( '<a>', aAttr ).text( subgroupName );

				var spanAttr = {
					'class': 'tp-aggregate-remove-button'
				};

				var $span = $( '<span>', spanAttr );
				
				var $ol = $( '#mw-tpa-grouplist-' + parentId );
				$ol.append( $( '<li>' ).append( $a.after( $span ) ) );

				// remove this group from the select.
				$selected.remove();
				$span.click( dissociate );
			}
		};

		var params = $.extend( getApiParams( $target ), {
			'do' : 'associate',
			group: subgroupId
		} );
		$.post( mw.util.wikiScript( 'api' ), params, successFunction );
	}

	function removeGroup( event ) {
		var	$target = $( event.target );

		function successFunction ( data, textStatus ) {
			if ( data.error ) {
				alert( data.error.info );
			} else {
				$( event.target ).parents( '.mw-tpa-group' ).remove();
			}
		}

		if ( confirm ( mw.msg( 'tpt-aggregategroup-remove-confirm' ) ) ) {
			var params = $.extend( getApiParams( $target ), {'do' : 'remove' } );
			$.post( mw.util.wikiScript( 'api' ), params, successFunction );
		}
	}

	$( '.tp-aggregate-add-button' ).click( associate );
	$( '.tp-aggregate-remove-button' ).click( dissociate );
	$( '.tp-aggregate-remove-ag-button' ).click( removeGroup );

	$( 'a.tpt-add-new-group' ).on ( "click", function( event ){
		$( 'div.tpt-add-new-group' ).removeClass( 'hidden' );
	} );

	$( '#tpt-aggregategroups-save' ). on ( "click", function( event ){
		var aggGroupNameInputName = $( 'input.tp-aggregategroup-add-name' ),
			aggGroupNameInputDesc = $( 'input.tp-aggregategroup-add-description' ),
			aggregateGroupName = aggGroupNameInputName.val(),
			aggregateGroupDesc = aggGroupNameInputDesc.val();

		// Empty the fields. If they are not emptied, then when another group
		// is added, the values will appear again. Bug 36296.
		aggGroupNameInputName.val( '' );
		aggGroupNameInputDesc.val( '' );

		var $select = $( 'div.mw-tpa-group select' );

		var successFunction = function( data, textStatus ) {
			if ( data.error ) {
				alert( data.error.info );
			}else{
				var aggregateGroupId = data.aggregategroups.aggregategroupId;
				var $removeSpan =  $( '<span>' ).attr( 'id', aggregateGroupId ).addClass( 'tp-aggregate-remove-ag-button' );
				var $div = $( "<div class='mw-tpa-group'>" )
					.append ( $( '<h2>' ).text( aggregateGroupName ) 
						.append ( $removeSpan ) ) 
					.append ( $('<p>').text( aggregateGroupDesc ) )
					.append ( $('<ol id=\'mw-tpa-grouplist-' + aggregateGroupId +'\'>') );
				$div.data( 'groupid', aggregateGroupId );
				$div.data( 'id', aggregateGroupId );
				if ( $select.length > 0 ){
					var $groupSelector = $( '<select>' ).attr('id', 'mw-tpa-groupselect-' + aggregateGroupId );
					$.each( data.aggregategroups.groups, function( key, value ) {
						$groupSelector.append( $( '<option>', { value : key } ).text( value ) ); 
					} );
					var $addButton =  $( $( 'input.tp-aggregate-add-button' )[0]).clone();
					$addButton.attr( 'id', aggregateGroupId );
					$div.append( $groupSelector ).append( $addButton );
					$addButton.click( associate );
					$removeSpan.click( removeGroup );
					$( 'div.tpt-add-new-group' ).addClass( 'hidden' );
				}else{
					// First group in the wiki. Cannot clone the group selector, just reload this time.
					location.reload();
				}
				$( 'a.tpt-add-new-group' ).before ( $div ) ;
			}
		};

		var params = {
			action: "aggregategroups",
			'do' : 'add',
			token: $( "#token" ).val(),
			groupname : aggregateGroupName,
			groupdescription: aggregateGroupDesc,
			format: "json"
		};
		$.post( mw.util.wikiScript( 'api' ), params, successFunction );
	} );
} ( jQuery, mediaWiki ) );
