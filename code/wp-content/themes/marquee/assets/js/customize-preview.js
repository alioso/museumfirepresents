/*global _:false, wp:false */

(function( $, wp ) {
	'use strict';

	var api = wp.customize,
		cssSettings = [ 'background_overlay_opacity', 'header_textcolor', 'marquee_background_color' ],
		stylesTemplate = wp.template( 'marquee-customizer-styles' ),
		$styles = $( '#marquee-custom-css' ),
		$siteDescription = $( '.site-description' ),
		$siteTitle = $( '.site-title a' );

	if ( ! $styles.length ) {
		$styles = $( 'head' ).append( '<style type="text/css" id="marquee-custom-css"></style>' )
							 .find( '#marquee-custom-css' );
	}

	function toggleHeaderText( value ) {
		var $headerText = $( '.site-title, .site-description' );

		if ( 'blank' === value ) {
			$headerText.css({
				'position': 'absolute',
				'clip': 'rect(1px 1px 1px 1px)'
			});
		} else {
			$headerText.css({
				'position': 'static',
				'clip': 'auto'
			});
		}
	}

	function updateCSS() {
		var css = stylesTemplate({
			backgroundColor: api( 'marquee_background_color' )(),
			backgroundOverlayOpacity: api( 'background_overlay_opacity' )() / 100,
			headerTextColor: api( 'header_textcolor' )()
		});

		$styles.html( css );
	}

	// Site title.
	api( 'blogname', function( value ) {
		value.bind(function( to ) {
			$siteTitle.text( to );
		});
	});

	// Site description.
	api( 'blogdescription', function( value ) {
		value.bind(function( to ) {
			$siteDescription.text( to );
		});
	});

	api( 'header_textcolor', function( setting ) {
		toggleHeaderText( setting() );
		setting.bind( toggleHeaderText );
	});

	// Update CSS when colors are changed.
	_.each( cssSettings, function( settingKey ) {
		api( settingKey, function( setting ) {
			setting.bind(function( value ) {
				updateCSS();
			});
		});
	});

})( jQuery, wp );
