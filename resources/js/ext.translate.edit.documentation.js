( function () {
	var warningDialog = new OO.ui.MessageDialog(),
		windowManager = new OO.ui.WindowManager(),
		dialogOptions = {
			title: mw.msg( 'tps-edit-sourcepage-ve-warning-title' ),
			message: mw.msg( 'tps-edit-sourcepage-ve-warning-text' ),
			actions: [
				{
					action: 'accept',
					label: mw.msg( 'tps-edit-sourcepage-ve-warning-button' ),
					flags: [ 'primary', 'progressive' ]
				}
			]
		},
		isWarningShown = false;

	$( 'body' ).append( windowManager.$element );
	windowManager.addWindows( [ warningDialog ] );

	$( function () {
		mw.hook( 've.activationComplete' ).add( function () {
			// eslint-disable-next-line no-undef
			var surface = ve.init.target.getSurface();
			if ( surface.getMode() === 'visual' ) {
				// Visual mode
				showWarning();
			}
		} );
	} );

	function showWarning() {
		if ( isWarningShown ) {
			return;
		}
		isWarningShown = true;
		windowManager.openWindow( warningDialog, dialogOptions );
	}
}() );
