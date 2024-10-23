/* eslint-disable */
/*!
 * Positions elements relative to other elements.
 * Borrowed from jquery.ui.position and updated to remove unused code.
 * Does not pollute the global jQuery or jQuery.ui objects
 * Does not support all options supported by jQuery.ui.position
 * Changes:
 * - The 'using' option is not supported
 * - Only 'collision': 'flip' is supported.
 * - In options, the 'of' parameter must always be provided
 * Upstream commit: https://github.com/jquery/jquery-ui/commit/0df6e658307f8936a477deb9674d643d18a2469b
 */
'use strict';

var cachedScrollbarWidth,
	abs = Math.abs,
	rhorizontal = /left|center|right/,
	rvertical = /top|center|bottom/,
	roffset = /[\+\-]\d+(\.[\d]+)?%?/,
	rposition = /^\w+/,
	rpercent = /%$/;

function getOffsets( offsets, width, height ) {
	return [
		parseFloat( offsets[ 0 ] ) * ( rpercent.test( offsets[ 0 ] ) ? width / 100 : 1 ),
		parseFloat( offsets[ 1 ] ) * ( rpercent.test( offsets[ 1 ] ) ? height / 100 : 1 )
	];
}

function parseCss( element, property ) {
	return parseInt( $.css( element, property ), 10 ) || 0;
}

function isWindow( obj ) {
	return obj != null && obj === obj.window;
}

function getDimensions( elem ) {
	var raw = elem[ 0 ];
	if ( raw.nodeType === 9 ) {
		return {
			width: elem.width(),
			height: elem.height(),
			offset: { top: 0, left: 0 }
		};
	}
	if ( isWindow( raw ) ) {
		return {
			width: elem.width(),
			height: elem.height(),
			offset: { top: elem.scrollTop(), left: elem.scrollLeft() }
		};
	}
	if ( raw.preventDefault ) {
		return {
			width: 0,
			height: 0,
			offset: { top: raw.pageY, left: raw.pageX }
		};
	}
	return {
		width: elem.outerWidth(),
		height: elem.outerHeight(),
		offset: elem.offset()
	};
}

function scrollbarWidth() {
	if ( cachedScrollbarWidth !== undefined ) {
		return cachedScrollbarWidth;
	}
	var w1, w2,
		div = $( '<div style=' +
			"'display:block;position:absolute;width:200px;height:200px;overflow:hidden;'>" +
			"<div style='height:300px;width:auto;'></div></div>" ),
		innerDiv = div.children()[ 0 ];

	$( 'body' ).append( div );
	w1 = innerDiv.offsetWidth;
	div.css( 'overflow', 'scroll' );

	w2 = innerDiv.offsetWidth;

	if ( w1 === w2 ) {
		w2 = div[ 0 ].clientWidth;
	}

	div.remove();

	return cachedScrollbarWidth = w1 - w2;
}

function getScrollInfo( within ) {
	var overflowX = within.isWindow || within.isDocument ? '' :
			within.element.css( 'overflow-x' ),
		overflowY = within.isWindow || within.isDocument ? '' :
			within.element.css( 'overflow-y' ),
		hasOverflowX = overflowX === 'scroll' ||
			( overflowX === 'auto' && within.width < within.element[ 0 ].scrollWidth ),
		hasOverflowY = overflowY === 'scroll' ||
			( overflowY === 'auto' && within.height < within.element[ 0 ].scrollHeight );
	return {
		width: hasOverflowY ? scrollbarWidth() : 0,
		height: hasOverflowX ? scrollbarWidth() : 0
	};
}

function getWithinInfo( element ) {
	var withinElement = $( element || window ),
		isElemWindow = isWindow( withinElement[ 0 ] ),
		isDocument = !!withinElement[ 0 ] && withinElement[ 0 ].nodeType === 9,
		hasOffset = !isElemWindow && !isDocument;
	return {
		element: withinElement,
		isWindow: isElemWindow,
		isDocument: isDocument,
		offset: hasOffset ? $( element ).offset() : { left: 0, top: 0 },
		scrollLeft: withinElement.scrollLeft(),
		scrollTop: withinElement.scrollTop(),
		width: withinElement.outerWidth(),
		height: withinElement.outerHeight()
	};
}

var flip = {
	left: function ( position, data ) {
		var within = data.within,
			withinOffset = within.offset.left + within.scrollLeft,
			outerWidth = within.width,
			offsetLeft = within.isWindow ? within.scrollLeft : within.offset.left,
			collisionPosLeft = position.left - data.collisionPosition.marginLeft,
			overLeft = collisionPosLeft - offsetLeft,
			overRight = collisionPosLeft + data.collisionWidth - outerWidth - offsetLeft,
			myOffset = data.my[ 0 ] === 'left' ?
				-data.elemWidth :
				data.my[ 0 ] === 'right' ?
					data.elemWidth :
					0,
			atOffset = data.at[ 0 ] === 'left' ?
				data.targetWidth :
				data.at[ 0 ] === 'right' ?
					-data.targetWidth :
					0,
			offset = -2 * data.offset[ 0 ],
			newOverRight,
			newOverLeft;

		if ( overLeft < 0 ) {
			newOverRight = position.left + myOffset + atOffset + offset + data.collisionWidth -
				outerWidth - withinOffset;
			if ( newOverRight < 0 || newOverRight < abs( overLeft ) ) {
				position.left += myOffset + atOffset + offset;
			}
		} else if ( overRight > 0 ) {
			newOverLeft = position.left - data.collisionPosition.marginLeft + myOffset +
				atOffset + offset - offsetLeft;
			if ( newOverLeft > 0 || abs( newOverLeft ) < overRight ) {
				position.left += myOffset + atOffset + offset;
			}
		}
	},
	top: function ( position, data ) {
		var within = data.within,
			withinOffset = within.offset.top + within.scrollTop,
			outerHeight = within.height,
			offsetTop = within.isWindow ? within.scrollTop : within.offset.top,
			collisionPosTop = position.top - data.collisionPosition.marginTop,
			overTop = collisionPosTop - offsetTop,
			overBottom = collisionPosTop + data.collisionHeight - outerHeight - offsetTop,
			top = data.my[ 1 ] === 'top',
			myOffset = top ?
				-data.elemHeight :
				data.my[ 1 ] === 'bottom' ?
					data.elemHeight :
					0,
			atOffset = data.at[ 1 ] === 'top' ?
				data.targetHeight :
				data.at[ 1 ] === 'bottom' ?
					-data.targetHeight :
					0,
			offset = -2 * data.offset[ 1 ],
			newOverTop,
			newOverBottom;
		if ( overTop < 0 ) {
			newOverBottom = position.top + myOffset + atOffset + offset + data.collisionHeight -
				outerHeight - withinOffset;
			if ( newOverBottom < 0 || newOverBottom < abs( overTop ) ) {
				position.top += myOffset + atOffset + offset;
			}
		} else if ( overBottom > 0 ) {
			newOverTop = position.top - data.collisionPosition.marginTop + myOffset + atOffset +
				offset - offsetTop;
			if ( newOverTop > 0 || abs( newOverTop ) < overBottom ) {
				position.top += myOffset + atOffset + offset;
			}
		}
	}
};

function positionElement( $targetElem, options ) {
	// Make a copy, we don't want to modify arguments
	options = $.extend( {}, options );

	var atOffset, targetWidth, targetHeight, targetOffset, basePosition, dimensions,

		// Make sure string options are treated as CSS selectors
		target = typeof options.of === 'string' ?
			$( document ).find( options.of ) :
			$( options.of ),

		within = getWithinInfo( options.within ),
		scrollInfo = getScrollInfo( within ),
		offsets = {};

	dimensions = getDimensions( target );
	if ( target[ 0 ].preventDefault ) {

		// Force left top to allow flipping
		options.at = 'left top';
	}
	targetWidth = dimensions.width;
	targetHeight = dimensions.height;
	targetOffset = dimensions.offset;

	// Clone to reuse original targetOffset later
	basePosition = $.extend( {}, targetOffset );

	// Force my and at to have valid horizontal and vertical positions
	// if a value is missing or invalid, it will be converted to center
	$.each( [ 'my', 'at' ], function () {
		var pos = ( options[ this ] || '' ).split( ' ' ),
			horizontalOffset,
			verticalOffset;

		if ( pos.length === 1 ) {
			pos = rhorizontal.test( pos[ 0 ] ) ?
				pos.concat( [ 'center' ] ) :
				rvertical.test( pos[ 0 ] ) ?
					[ 'center' ].concat( pos ) :
					[ 'center', 'center' ];
		}
		pos[ 0 ] = rhorizontal.test( pos[ 0 ] ) ? pos[ 0 ] : 'center';
		pos[ 1 ] = rvertical.test( pos[ 1 ] ) ? pos[ 1 ] : 'center';

		// Calculate offsets
		horizontalOffset = roffset.exec( pos[ 0 ] );
		verticalOffset = roffset.exec( pos[ 1 ] );
		offsets[ this ] = [
			horizontalOffset ? horizontalOffset[ 0 ] : 0,
			verticalOffset ? verticalOffset[ 0 ] : 0
		];

		// Reduce to just the positions without the offsets
		options[ this ] = [
			rposition.exec( pos[ 0 ] )[ 0 ],
			rposition.exec( pos[ 1 ] )[ 0 ]
		];
	} );

	if ( options.at[ 0 ] === 'right' ) {
		basePosition.left += targetWidth;
	} else if ( options.at[ 0 ] === 'center' ) {
		basePosition.left += targetWidth / 2;
	}

	if ( options.at[ 1 ] === 'bottom' ) {
		basePosition.top += targetHeight;
	} else if ( options.at[ 1 ] === 'center' ) {
		basePosition.top += targetHeight / 2;
	}

	atOffset = getOffsets( offsets.at, targetWidth, targetHeight );
	basePosition.left += atOffset[ 0 ];
	basePosition.top += atOffset[ 1 ];

	return $targetElem.each( function () {
		var collisionPosition,
			elem = $( this ),
			elemWidth = elem.outerWidth(),
			elemHeight = elem.outerHeight(),
			marginLeft = parseCss( this, 'marginLeft' ),
			marginTop = parseCss( this, 'marginTop' ),
			collisionWidth = elemWidth + marginLeft + parseCss( this, 'marginRight' ) +
				scrollInfo.width,
			collisionHeight = elemHeight + marginTop + parseCss( this, 'marginBottom' ) +
				scrollInfo.height,
			position = $.extend( {}, basePosition ),
			myOffset = getOffsets( offsets.my, elem.outerWidth(), elem.outerHeight() );

		if ( options.my[ 0 ] === 'right' ) {
			position.left -= elemWidth;
		} else if ( options.my[ 0 ] === 'center' ) {
			position.left -= elemWidth / 2;
		}

		if ( options.my[ 1 ] === 'bottom' ) {
			position.top -= elemHeight;
		} else if ( options.my[ 1 ] === 'center' ) {
			position.top -= elemHeight / 2;
		}

		position.left += myOffset[ 0 ];
		position.top += myOffset[ 1 ];

		collisionPosition = {
			marginLeft: marginLeft,
			marginTop: marginTop
		};

		$.each( [ 'left', 'top' ], function ( i, dir ) {
			flip[ dir ]( position, {
				targetWidth: targetWidth,
				targetHeight: targetHeight,
				elemWidth: elemWidth,
				elemHeight: elemHeight,
				collisionPosition: collisionPosition,
				collisionWidth: collisionWidth,
				collisionHeight: collisionHeight,
				offset: [ atOffset[ 0 ] + myOffset[ 0 ], atOffset[ 1 ] + myOffset[ 1 ] ],
				my: options.my,
				at: options.at,
				within: within,
				elem: elem
			} );
		} );

		elem.offset( position );
	} );
}

module.exports = positionElement;
