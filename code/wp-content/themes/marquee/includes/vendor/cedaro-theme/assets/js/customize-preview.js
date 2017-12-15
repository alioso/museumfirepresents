(function( $, api ) {
	'use strict';

	api( 'site_logo', function( setting ) {
		var $body = $( 'body' );

		setting.bind( function( value ) {
			// Use a placeholder to prevent browsers from requesting the
			// current page if the src attribute is empty.
			var url = value.url || 'data:image/gif;base64,R0lGODlhAQABAAAAADs=';
			$( '.site-logo' ).attr( 'src', url ).toggle( !! value.url );
			$body.toggleClass( 'has-site-logo', !! value.url );
		});
	});

	api( 'site_logo_header_text', function( setting ) {
		setting.bind( function( value ) {
			var $headerText = $( '.site-title, .site-description' );

			if ( value ) {
				$headerText.css({
					'position': 'static',
					'clip': 'auto'
				});
			} else {
				$headerText.css({
					'position': 'absolute',
					'clip': 'rect(1px 1px 1px 1px)'
				});
			}
		});
	});

})( jQuery, wp.customize );
