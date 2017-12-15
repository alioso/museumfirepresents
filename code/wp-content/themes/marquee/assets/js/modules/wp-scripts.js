/*jshint browserify:true */

'use strict';

var WPEnqueues = require ( './wp-enqueues' ),
	inherits = require( 'inherits' );

function WPScripts() {
	WPEnqueues.call( this );
	this.done = [];
}

inherits( WPScripts, WPEnqueues );

function loadScript( src, where, callback ) {
	var scriptTag = document.createElement( 'script' ),
		hasLoaded = false;

	if ( 'function' === typeof where ) {
		callback = where;
	}

	if ( 'string' !== typeof where ) {
		where = 'head';
	}

	scriptTag.setAttribute( 'src', src );

	// If there is a callback, run it when the script is loaded.
	if ( callback ) {
		scriptTag.onreadystatechange = scriptTag.onload = function () {
			if ( ! hasLoaded ) {
				callback();
			}
			hasLoaded = true;
		};
	}

	document.getElementsByTagName( where )[0].appendChild( scriptTag );
}

WPScripts.prototype.load = function( script, callback ) {
	var dataContent, dataTag,
		where = script.footer ? 'body' : 'head';

	// Output extra data, if present.
	if ( script.extra_data ) {
		dataTag = document.createElement( 'script' );
		dataContent = document.createTextNode( '//<![CDATA[ \n' + script.extra_data + '\n//]]>' );

		dataTag.type = 'text/javascript';
		dataTag.appendChild( dataContent );

		document.getElementsByTagName( where )[0].appendChild( dataTag );
	}

	loadScript( script.src, where, callback );

	return this;
};

module.exports = WPScripts;
