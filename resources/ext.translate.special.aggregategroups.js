jQuery( document ).ready( function ( $ ) {
	"use strict";
	
	function getApiParams( $target ) {
		return {
			action: 'aggregategroups',
			token: $( '#token' ).val(),
			aggregategroup: $target.parents( '.mw-tpa-group' ).data( 'groupid' ),
			format: "json"
		};
	}

	function associate( event ) {
		var
			$target = $( event.target ),
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
					'class': 'tp-aggregate-remove-button',
					'data-groupid': subgroupId
				};

				var $span = $( '<span>', spanAttr )
				
				var $ol = $( '#mw-tpa-grouplist-' + parentId );
				$ol.append( $( '<li>' ).append( $a.after( $span ) ) );

				// remove this group from the select.
				$selected.remove();
				$span.click( dissociate );
			}
		};
		
		var params = $.extend( getApiParams( $target ), {
			'do' : 'associate',
			group: subgroupId,
		} );
		$.post( mw.util.wikiScript( 'api' ), params, successFunction );
	}

	function dissociate( event ) {
		var
			$target = $( event.target ),
			parentId = $target.parents( '.mw-tpa-group' ).data( 'id' ),
			$select = $( '#mw-tpa-groupselect-' + parentId );

		function successFunction( data, textStatus ) {
			if ( data.error ) {
				alert( data.error.info );
			}  else {
				$( '<option>', { value: $target.data( 'groupid' ) } )
					.text( $target.parent( 'a' ).text() )
					.appendTo( $select );
				$target.parent( 'li' ).remove();
			}
		};
		
		var params = $.extend( getApiParams( $target ), {
			'do' : 'dissociate',
			group: $target.data( 'groupid' ),
		} );
		$.post( mw.util.wikiScript( 'api' ), params, successFunction );
	}

	function removeGroup( event ) {
		var
			$target = $( event.target ),
			parentId = $target.parent( '.mw-tpa-group' ).data( 'groupid' );

		function successFunction ( data, textStatus ) {
			if ( data.error ) {
				alert( data.error.info );
			} else {
				$( event.target ).parents( '.mw-tpa-group' ).remove();
			}
		};

		var params = $.extend( getApiParams( $target ), {'do' : 'remove' } );
		$.post( mw.util.wikiScript( "api" ), params, successFunction );
	}

	$( '.tp-aggregate-add-button' ).click( associate );
	$( '.tp-aggregate-remove-button' ).click( dissociate );
	$( '.tp-aggregate-remove-ag-button' ).click( removeGroup );
	
	$( 'a.tpt-add-new-group' ).on ( "click", function( event ){
		$( 'div.tpt-add-new-group' ).removeClass( 'hidden' );
	} );

	$( '#tpt-aggregategroups-save' ). on ( "click", function( event ){
		var aggregateGroup = $( 'input.tp-aggregategroup-add-name' ).val().toLowerCase().replace( ' ', '_');
		var aggregateGroupName = $( 'input.tp-aggregategroup-add-name' ).val();
		var aggregateGroupDesc = $( 'input.tp-aggregategroup-add-description' ).val();
		var $select = $( 'select.tp-aggregate-group-chooser' );

		var successFunction = function( data, textStatus ) {
			if ( data.error ) {
				alert( data.error.info );
			}else{
				var $removeSpan =  $( '<span>' ).attr( 'id', aggregateGroup ).addClass( 'tp-aggregate-remove-ag-button' );
				var $div = $( "<div class='mw-tpa-group'>" )
					.append ( $( '<h2>' ).text( aggregateGroupName ) 
						.append ( $removeSpan ) ) 
					.append ( $('<p>').text( aggregateGroupDesc ) )
					.append ( $('<ol id=\'mw-tpa-grouplist-'+aggregateGroup+'\'>') );

				if ( $select.length > 0 ){
					var $groupSelector = $( $( 'select.tp-aggregate-group-chooser')[0] ).clone();
					$groupSelector.attr('id', 'tp-aggregate-groups-select-' + aggregateGroup);
					var $addButton =  $( $( 'input.tp-aggregate-add-button')[0]).clone();
					$addButton.attr( 'id', aggregateGroup);
					$div.append( $groupSelector ).append( $addButton );
					$addButton.on ( "click", function( event ){ associate(event); } );
					$removeSpan.on ( "click", function( event ){ removeGroup(event); } );
					$( 'div.tpt-add-new-group' ).addClass('hidden');
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
			aggregategroup: aggregateGroup,
			groupname : aggregateGroupName,
			groupdescription: aggregateGroupDesc,
			format: "json"
		};
		$.post( mw.util.wikiScript( "api" ), params, successFunction );
	} )
} );
