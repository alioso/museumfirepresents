/*jshint browserify:true, evil:true */

'use strict';

var IEVersion,
	WPEnqueues = require ( './wp-enqueues' ),
	inherits = require( 'inherits' ),
	isIE = ( -1 !== navigator.userAgent.search( 'MSIE' ) );

// IE requires special handling.
if ( isIE ) {
	IEVersion = navigator.userAgent.match( /MSIE\s?(\d+)\.?\d*;/ );
	IEVersion = parseInt( IEVersion[1], 10 );
}

function WPStyles() {
	WPEnqueues.call( this );
	this.done = [];
}

inherits( WPStyles, WPEnqueues );

WPStyles.prototype.load = function( style ) {
	var tag = document.createElement( 'link' );

	tag.rel = 'stylesheet';
	tag.href = style.src;
	tag.id = style.handle + '-css';

	// Destroy link tag if a conditional statement is present and either the
	// browser isn't IE, or the conditional doesn't evaluate true.
	if ( style.conditional && ( ! isIE || ! eval( style.conditional.replace( /%ver/g, IEVersion ) ) ) ) {
		tag = false;
	}

	// Append link tag if necessary
	if ( tag ) {
		document.getElementsByTagName( 'head' )[0].appendChild( tag );
	}

	return this;
};

module.exports = WPStyles;
