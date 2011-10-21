jQuery( document ).ready( function( $ ) {
	var $buttons = $( ".mw-translate-messagereviewbutton" );
	$buttons.each( function() {
		var $b = $(this);
		$b.click( function() {
			var successFunction = function( data, textStatus ) {
				console.log( data, textStatus );
				if ( data.error ) {
					var reason = mw.msg( "translate-messagereview-apierror-" + data.error.code );
					$b.val( mw.msg( "translate-messagereview-failure", reason ) );
				} else {
					$b.val( mw.msg( "translate-messagereview-done" ) );
				}
			};
			
			var params = {
				action: "translationreview",
				token: $b.data( "token" ),
				revision: $b.data( "revision" ),
				format: "json"
			};
			$b.val( mw.msg( "translate-messagereview-progress" ) );
			$b.prop( "disabled", true );
			$.post( mw.util.wikiScript( "api" ), params, successFunction );
			
		} );
	} );
} );
