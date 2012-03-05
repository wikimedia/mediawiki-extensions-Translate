jQuery( function( $ ) {

	$( document ).ready( function () {

		function associate( event ){
			var aggregategroup = event.target.id;
			var selected = $( '#tp-aggregate-groups-select-'+ aggregategroup + ' option:selected' ).text();
			var group = $( '#tp-aggregate-groups-select-'+ aggregategroup + ' option:selected' ).val();
			var $select= $( 'select.tp-aggregate-group-chooser' ) ;

			var successFunction = function( data, textStatus ) {
				if ( data.error ) {
					alert( data.error.info );
				}else{
					$( '#tp-aggregate-groups-ol-'+ aggregategroup ).append( '<li><a id='+group+' href='+selected+'>'+selected+'</a><span class=\'tp-aggregate-remove-button\' id='+group+'></span></li>' );
					$( 'option#'+ group ).remove();
					$( 'span#'+group ).on ( "click", function(event){ dissociate(event); } );
				}
			};

			var params = {
				action: "aggregategroups",
				'do' : 'associate',
				token: $( "#token" ).val(),
				group: group,
				aggregategroup: aggregategroup,
				format: "json"
			};
			$.post( mw.util.wikiScript( "api" ), params, successFunction );
		}

		function dissociate(event){
			var group = event.target.id;
			var selected = $( 'a#'+group ).text();
			var $select= $( 'select.tp-aggregate-group-chooser' ) ;
			var aggregategroup = $( 'a#'+group ).closest( 'div' ).find( 'h2' ).attr( 'id' );

			var successFunction = function( data, textStatus ) {
				if ( data.error ) {
					alert( data.error.info );
				}else{
					$select.each( function(){
						$( this ).append( '<option value="'+group+'">'+selected+'</option>' );
					} );
					$( 'span#'+ group ).closest( 'li' ).remove();
				}
			};

			var params = {
				action: "aggregategroups",
				'do' : 'dissociate',
				token: $( "#token" ).val(),
				group: group,
				aggregategroup: aggregategroup,
				format: "json"
			};
			$.post( mw.util.wikiScript( "api" ), params, successFunction );
		}

		function removeGroup(event){
			var aggregategroup = event.target.id;
			var $select= $( 'select.tp-aggregate-group-chooser') ;

			var successFunction = function( data, textStatus ) {
				if ( data.error ) {
					alert( data.error.info );
				}else{
					$( 'span#'+ aggregategroup ).parent().parent().find('li a').each(function(){
						$groupId = $( this ).attr('id');
						$groupName = $( this ).text();
						$select.each( function(){
							$ (this ).append('<option value="'+$groupId+'">'+$groupName+'</option>');
						} );
					});
					$( 'span#'+ aggregategroup ).closest('div#tpt-aggregate-group').remove();
				}
			};

			var params = {
				action: "aggregategroups",
				'do' : 'remove',
				token: $( "#token" ).val(),
				aggregategroup: aggregategroup,
				format: "json"
			};
			$.post( mw.util.wikiScript( "api" ), params, successFunction );
		}

		$( 'input.tp-aggregate-add-button' ).on ( "click", function( event ){
			associate(event);
		} );

		$( 'span.tp-aggregate-remove-button' ).on ( "click", function( event ){
			dissociate(event);
		 } );

		$( 'span.tp-aggregate-remove-ag-button' ).on ( "click", function( event ){
			removeGroup(event);
		 } );

		$( 'a.tpt-add-new-group' ).on ( "click", function( event ){
			$( 'div.tpt-add-new-group' ).removeClass( 'hidden' );
		} );

		$( '#tpt-aggregategroups-save' ). on ( "click", function( event ){
			var aggregateGroup = $( 'input.tp-aggregategroup-add-name' ).val().toLowerCase().replace( ' ', '_');
			var aggregateGroupName = $( 'input.tp-aggregategroup-add-name' ).val();
			var aggregateGroupDesc = $( 'input.tp-aggregategroup-add-description' ).val();
			var $select= $( 'select.tp-aggregate-group-chooser' ) ;

			var successFunction = function( data, textStatus ) {
				if ( data.error ) {
					alert( data.error.info );
				}else{
					$removeSpan  =  $( '<span>' ).attr( 'id', aggregateGroup ).addClass( 'tp-aggregate-remove-ag-button' );
					$div = $( "<div id='tpt-aggregate-group'>" )
						.append ( $( '<h2>' ).attr( 'id', aggregateGroup ).text( aggregateGroupName ) 
							.append ( $removeSpan ) ) 
						.append ( $('<p>').text( aggregateGroupDesc ) )
						.append ( $('<ol id=\'tp-aggregate-groups-ol-'+aggregateGroup+'\'>') );

					if (  $select.length > 0 ){
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
} );
