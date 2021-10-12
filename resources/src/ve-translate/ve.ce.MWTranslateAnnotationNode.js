/*!
 * VisualEditor ContentEditable MWTranslateAnnotationNode class.
 */

/**
 * ContentEditable MediaWiki translate annotation node.
 *
 * @class
 * @abstract
 * @extends ve.ce.MWAnnotationNode
 *
 * @constructor
 * @param {ve.dm.MWTranslateAnnotationNode} model Model to observe
 * @param {Object} [config] Configuration options
 */
ve.ce.MWTranslateAnnotationNode = function VeCeMWTranslateAnnotationNode() {
	// Parent constructor
	ve.ce.MWTranslateAnnotationNode.super.apply( this, arguments );
};

/* Inheritance */

OO.inheritClass( ve.ce.MWTranslateAnnotationNode, ve.ce.MWAnnotationNode );

/* Static members */

ve.ce.MWTranslateAnnotationNode.static.name = 'mwTranslateAnnotation';

/* Registration */

ve.ce.nodeFactory.register( ve.ce.MWTranslateAnnotationNode );
