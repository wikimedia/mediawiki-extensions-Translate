/*!
 * @author Santhosh Thottingal
 * jQuery autocomplete based multiple selector for input box.
 * Autocompleted values will be available in input filed as comma separated values.
 * The values for autocompletion is from the language selector in this case.
 * The input field is created in PHP code.
 * Credits: http://jqueryui.com/demos/autocomplete/#multiple
 */
jQuery( function ( $ ) {
	'use strict';

	// jscs:disable disallowDanglingUnderscores

	$.widget( 'ui.multiselectautocomplete', {
		options: {
			inputbox: null // a jQuery selector for the input box where selections are written.
			// TODO can have more options.
		},
		_create: function () {
			var self, select, options, input;

			self = this;
			select = this.element.hide();
			options = this.options;

			function split( val ) {
				return val.split( /,\s*/ );
			}

			input = this.input = $( options.inputbox ).autocomplete( {
				delay: 0,
				minLength: 0,
				source: function ( request, response ) {
					var term, matcher;

					term = split( request.term ).pop();
					matcher = new RegExp( $.ui.autocomplete.escapeRegex( term ), 'i' );

					response( select.children( 'option' ).map( function () {
						var text = $( this ).text(),
							value = $( this ).val(),
							term = split( request.term ).pop();

						if ( this.value && ( !request.term || matcher.test( text ) ) ) {
							return {
								label: text.replace(
									new RegExp(
										'(?![^&;]+;)(?!<[^<>]*)(' +
											$.ui.autocomplete.escapeRegex( term ) +
											')(?![^<>]*>)(?![^&;]+;)', 'gi'
									), '<strong>$1</strong>' ),
								value: value,
								option: this
							};
						}
					} ) );
				},
				select: function ( event, ui ) {
					var terms;

					ui.item.option.selected = true;
					self._trigger( 'selected', event, {
						item: ui.item.option
					} );
					terms = split( $( this ).val() );
					// remove the current input
					terms.pop();
					// add the selected item
					terms.push( ui.item.value );
					// add placeholder to get the comma-and-space at the end
					terms.push( '' );
					$( this ).val( terms.join( ', ' ) );
					return false;
				}
			} );

			input.data( 'autocomplete' )._renderItem = function ( ul, item ) {
				return $( '<li>' )
					.data( 'item.autocomplete', item )
					.append( '<a>' + item.label + '</a>' )
					.appendTo( ul );
			};
		}, // End of _create

		destroy: function () {
			this.input.remove();
			this.element.show();
			$.Widget.prototype.destroy.call( this );
		}
	} );
} );
