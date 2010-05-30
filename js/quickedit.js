/**
 * JavaScript that implements the Ajax translation interface, which was at the
 * time of writing this probably the biggest usability problem in the extension.
 * Most importantly, it speeds up translating and keeps the list of translatable
 * messages open. It also allows multiple translation dialogs, for doing quick
 * updates to other messages or documentation, or translating multiple languages
 * simultaneously together with the "In other languages" display included in
 * translation helpers and implemented by utils/TranslationhHelpers.php.
 * The form itself is implemented by utils/TranslationEditPage.php, which is
 * called from Special:Translate/editpage?page=Namespace:pagename.
 *
 * TODO list:
 * * On succesful save, update the MessageTable display too.
 * * Integrate the (new) edittoolbar
 * * Autoload ui classes
 * * Instead of hc'd onscript, give them a class and use necessary triggers
 * * Live-update the checks assistant
 *
 * @author Niklas Laxström
 * @copyright Copyright © 2009-2010 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

function trlOpenJsEdit( page, group ) {
	var url = wgScript + "?title=Special:Translate/editpage&page=$1&loadgroup=$2";
	url = url.replace( "$1", page ).replace( "$2", group );
	var id = "jsedit" +  page.replace( /[^a-zA-Z0-9_]/g, '_' );

	var dialog = jQuery("#"+id);
	if ( dialog.size() > 0 ) {
		dialog.dialog("option", "position", "top" );
		dialog.dialog("open");
		return false;
	}

	jQuery('<div/>').attr('id', id).appendTo(jQuery('body'));
	var dialog = jQuery("#"+id);

	dialog.load(url, false, function() {
		var form = jQuery("#"+ id + " form");

		form.find( ".mw-translate-next" ).click( function() {
			trlLoadNext( page );
		});

		form.find( ".mw-translate-skip" ).click( function() {
			trlLoadNext( page );
			dialog.dialog("close");
			return false;
		});

		form.find( ".mw-translate-history" ).click( function() {
			window.open( wgServer + wgScript + "?action=history&title=" + form.find( "input[name=title]" ).val() );
			return false;
		});

		form.find( ".mw-translate-edit-area" ).focus();

		form.ajaxForm({
			dataType: "json",
			success: function(json) {
				if ( json.error ) {
					alert( json.error.info + " (" + json.error.code +")" );
				} else if ( json.edit.result === "Failure" ) {
					alert(trlMsgSaveFailed);
				} else if ( json.edit.result === "Success" ) {
					dialog.dialog("close");
				} else {
					alert(trlMsgSaveFailed);
				}
			}
		});
	});

	dialog.dialog({
		bgiframe: true,
		width: parseInt(trlVpWidth()*0.8),
		title: page,
		position: "top"
	});

	return false;
}

function trlVpWidth() {
	return window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
}

function trlLoadNext( title ) {
	var page = title.replace( /[^:]+:/, "");
	var namespace = title.replace( /:.*/, "");
	var found = false;
	for ( key in trlKeys ) {
		value = trlKeys[key];
		if (found) {
			return trlOpenJsEdit( namespace + ":" + value );
		} else if( page === value ) {
			found = true;
		}
	}
	alert(trlMsgNoNext);
	return;
}
