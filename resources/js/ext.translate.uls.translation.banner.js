const EntrypointRegistry = require( 'ext.uls.rewrite.entrypoints' );
const { ENTRYPOINT_TYPE, ULS_MODE } = EntrypointRegistry;
const { cdxIconSettings } = require( './ext.translate.uls.translation.banner.icons.json' );

EntrypointRegistry.register( ENTRYPOINT_TYPE.EMPTY_LIST, {
	id: 'translation-settings-banner',
	shouldShow: () => true,
	getConfig: () => ( {
		label: mw.msg( 'tpt-translation-settings-page-title' ),
		icon: cdxIconSettings,
		url: mw.util.getUrl( 'Special:PageTranslation', {
			do: 'settings',
			target: mw.config.get( 'wgPageName' )
		} )
	} )
}, ULS_MODE.CONTENT );
