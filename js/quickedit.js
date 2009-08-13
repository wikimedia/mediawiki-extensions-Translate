function trlOpenJsEdit( page ) {
	var url = wgScript + "?title=Special:Translate/editpage&page=" + page + "&uselang=" + wgUserLanguage;
	var id = "jsedit" +  page.replace( /[^a-zA-Z0-9_]/g, '_' );

	var dialog = jQuery("#"+id);
	if ( dialog.size() > 0 ) {
		dialog.dialog("option", "position", "top" );
		dialog.dialog("open");
		return false;
	}

	var div = jQuery('<div id=' + id + '></div>');
	div.appendTo(document.body);

	var dialog = jQuery("#"+id);

	dialog = dialog.load(url, false, function() {
		var form = jQuery("#"+ id + " form");
		var textarea = form.find( ".mw-translate-edit-area" );
		textarea.width(textarea.width()-4);
		form.ajaxForm({
			datatype: "json",
			success: function(json) {
				json = JSON.parse(json);
				if ( json.error ) {
					alert( json.error.info + " (" + json.error.code +")" );
				} else if ( json.edit.result == "Failure" ) {
					alert( "Extension error. Copy your text and try normal edit." );
				} else if ( json.edit.result == "Success" ) {
					//alert( "Saved!" );
					dialog.dialog("close");
					dialog.dialog("destroy");
				} else {
					alert( "Unknown error." );
				}
			},
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