/*!
 * VisualEditor DataModel MWTranslateAnnotationNode class.
 */

/**
 * DataModel MediaWiki translate annotation node.
 *
 * @class
 * @abstract
 * @extends ve.dm.MWAnnotationNode
 *
 * @constructor
 * @param {Object} [element] Reference to element in linear model
 * @param {ve.dm.Node[]} [children]
 */
ve.dm.MWTranslateAnnotationNode = function VeDmMWTranslateAnnotationNode() {
	// Parent constructor
	ve.dm.MWTranslateAnnotationNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.dm.MWTranslateAnnotationNode, ve.dm.MWAnnotationNode );

/* Static members */

ve.dm.MWTranslateAnnotationNode.static.name = 'mwTranslateAnnotation';

ve.dm.MWTranslateAnnotationNode.static.matchRdfaTypes = [
	'mw:Annotation/translate',
	'mw:Annotation/translate/End',
	'mw:Annotation/tvar',
	'mw:Annotation/tvar/End'
];

/* Methods */

ve.dm.MWTranslateAnnotationNode.static.toDataElement = function ( domElements ) {
	// 'Parent' method
	var element = ve.dm.MWTranslateAnnotationNode.super.static.toDataElement.call( this, domElements );

	element.type = 'mwTranslateAnnotation';
	return element;
};

ve.dm.MWTranslateAnnotationNode.prototype.getWikitextTag = function () {
	var map = {
		'mw:Annotation/translate': '<translate>',
		'mw:Annotation/translate/End': '</translate>',
		'mw:Annotation/tvar': '<tvar>',
		'mw:Annotation/tvar/End': '</tvar>'
	};
	return map[ this.getAttribute( 'type' ) ];
};

/* Registration */

ve.dm.modelRegistry.register( ve.dm.MWTranslateAnnotationNode );
