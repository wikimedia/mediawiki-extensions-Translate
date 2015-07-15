/*
 * Autocomplete search operators.
 */
( function ( mw, $ ) {
	function autocompleteOperators( request, response ) {
		var operators = [ 'language:', 'group:' ],
			result = [],
			lastterm = request.term.split( ' ' ).pop();

		$.each( operators, function ( index, value ) {
			var pos = value.indexOf( lastterm );
			if ( pos === 0 ) {
				result.push( value );
			}
		} );
		response( result );
	};
	$( '.tux-searchpage .searchinputbox' )
		.autocomplete( {
			source: autocompleteOperators,
			select: function ( event, ui ) {
				var $value = $( this ).val(),
					operators = $value.split( ' ' );

				operators.pop();
				operators.push( ui.item.value );

				$( this ).val( operators.join( ' ' ) );
				return false;
			},

			focus: function (event, ui) {
			       event.preventDefault();
			}
		} );
}( mediaWiki, jQuery ) );
