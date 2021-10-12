/*!
 * VisualEditor MWTranslateAnnotationContextItem class.
 */

/**
 * Context item for a MWTranslateAnnotation
 *
 * @class
 * @extends ve.ui.MWAnnotationContextItem
 *
 * @constructor
 * @param {ve.ui.Context} context Context item is in
 * @param {ve.dm.Model} model Model item is related to
 * @param {Object} config Configuration options
 */
ve.ui.MWTranslateAnnotationContextItem = function VeUiMWTranslateAnnotationContextItem() {
	// Parent constructor
	ve.ui.MWTranslateAnnotationContextItem.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.ui.MWTranslateAnnotationContextItem, ve.ui.MWAnnotationContextItem );

/* Static Properties */

ve.ui.MWTranslateAnnotationContextItem.static.name = 'mwTranslateAnnotation';

ve.ui.MWTranslateAnnotationContextItem.static.modelClasses = [
	ve.dm.MWTranslateAnnotationNode
];

/* Methods */

ve.ui.MWTranslateAnnotationContextItem.prototype.getLabelMessage = function () {
	var map = {
		'mw:Annotation/translate': 'visualeditor-annotations-translate-start',
		'mw:Annotation/translate/End': 'visualeditor-annotations-translate-end',
		'mw:Annotation/tvar': 'visualeditor-annotations-tvar-start',
		'mw:Annotation/tvar/End': 'visualeditor-annotations-tvar-end'
	};

	var type = this.model.getAttribute( 'type' );
	// eslint-disable-next-line mediawiki/msg-doc
	var msg = mw.message( map[ type ] );
	return msg.text();
};

ve.ui.MWTranslateAnnotationContextItem.prototype.getDescriptionMessage = function () {
	var type = this.model.getAttribute( 'type' );
	if ( type.indexOf( '/End', type.length - 4 ) !== -1 ) {
		return '';
	}
	var map = {
		'mw:Annotation/translate': 'visualeditor-annotations-translate-description',
		'mw:Annotation/tvar': 'visualeditor-annotations-tvar-description'
	};

	// eslint-disable-next-line mediawiki/msg-doc
	var msg = mw.message( map[ type ] );
	return msg.parseDom();
};

ve.ui.contextItemFactory.register( ve.ui.MWTranslateAnnotationContextItem );
