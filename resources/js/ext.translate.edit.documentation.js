( function () {
	var isVeEditingSupported = mw.config.get( 'wgVersion' ) >= '1.38';
	// TODO load ext.translate.ve unconditionally after 1.39 release: T295203
	// (see gerrit I7a55a09514110fa7d290d6f03ce9c0b7962c9140; this should be
	// loaded from extension.json, not from here)
	if ( isVeEditingSupported ) {
		mw.loader.using( 'ext.visualEditor.targetLoader' ).then( function () {
			mw.libs.ve.targetLoader.addPlugin( 'ext.translate.ve' );
		} );
	} else {
		var warningTitle = mw.msg( 'tps-edit-sourcepage-ve-warning-title' );
		var warningText = mw.msg( 'tps-edit-sourcepage-ve-warning-text' );

		var warningDialog = new OO.ui.MessageDialog(),
			windowManager = new OO.ui.WindowManager(),
			dialogOptions = {
				title: warningTitle,
				message: warningText,
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
					if ( !isWarningShown ) {
						isWarningShown = true;
						windowManager.openWindow( warningDialog, dialogOptions );
					}
				}
			} );
		} );
	}
}() );
