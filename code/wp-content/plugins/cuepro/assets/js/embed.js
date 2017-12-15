(function( window, undefined ) {
	'use strict';

	var iframes = document.getElementsByTagName( 'iframe' );

	window.addEventListener( 'message', function( e ) {
		var iframe;

		if ( 'height' === e.data.message ) {
			iframe = iframes[ e.data.index ];
			if ( 'undefined' !== typeof iframe ) {
				iframe.height = parseInt( e.data.value, 10 );
				iframe.scrolling = 'no';
			}
		}
	} );

	function pollIframe( i ) {
		iframes[ i ].onload = iframes[ i ].onreadystatechange = function() {
			if ( this.readyState && 'complete' !== this.readyState && 'loaded' !== this.readyState ) {
				return;
			}

			setInterval( function() {
				// Send a message to the iframe to ask it to return its height.
				iframes[ i ].contentWindow.postMessage({
					index: i,
					message: 'height'
				}, '*' );
			}, 500 );
		};
	}

	if ( iframes.length ) {
		for ( var i = 0; i < iframes.length; i ++ ) {
			if ( -1 === iframes[ i ].className.indexOf( 'cue-embed' ) ) {
				continue;
			}

			pollIframe( i );
		}
	}
})( this );
