/*
 * @author Santhosh Thottingal
 * jQuery autocomplete based multiple selector for input box.
 * Autocompleted values will be available in input filed as comma separated values.
 * The values for autocompletion is from the language selector in this case.
 * The input field is created in PHP code.
 * Credits: http://jqueryui.com/demos/autocomplete/#multiple
 */
jQuery( function( $ ) {
	$( "#wpUserLanguage" ).multiselectautocomplete( { inputbox : '#tpt-prioritylangs' } );
} );
