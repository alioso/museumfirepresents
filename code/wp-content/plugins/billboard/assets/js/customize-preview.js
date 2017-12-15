/*global _:false, wp:false */

(function( $, wp ) {
	'use strict';

	var api = wp.customize,
		$body = $( 'body' ),
		cssSettings = [
			'billboard[background_color]',
			'billboard[background_image]',
			'billboard[background_overlay_opacity]',
			'billboard[logo_width]'
		],
		stylesTemplate = wp.template( 'billboard-customizer-styles' ),
		$styles = $( '#billboard-custom-css' );

	if ( ! $styles.length ) {
		$styles = $( 'head' ).append( '<style type="text/css" id="billboard-custom-css"></style>' )
							 .find( '#billboard-custom-css' );
	}

	api( 'billboard[title]', function( setting ) {
		var $title = $( '.billboard-title' );
		setting.bind(function( value ) {
			$title.text( value );
		});
	});

	api( 'billboard[tagline]', function( setting ) {
		var $tagline = $( '.billboard-tagline' );
		setting.bind(function( value ) {
			$tagline.text( value );
		});
	});

	api( 'billboard[display_header_text]', function( setting ) {
		setting.bind(function( value ) {
			var $headerText = $( '.billboard-title, .billboard-tagline' );

			if ( value ) {
				$headerText.css({
					'clip': 'auto',
					'height': 'auto',
					'position': 'static',
					'width': 'auto'
				});
			} else {
				$headerText.css({
					'position': 'absolute',
					'clip': 'rect(1px 1px 1px 1px)'
				});
			}
		});
	});

	api( 'billboard[layout]', function( setting ) {
		setting.bind(function( value ) {
			$body.removeClass(function( index, classes ) {
				return ( classes.match( /(^|\s)billboard-layout-\S+/g ) || [] ).join( ' ' );
			});

			value = value || 'signature';
			$body.addClass( 'billboard-layout-' + value );
		});
	});

	api( 'billboard[text_scheme]', function( setting ) {
		setting.bind(function( value ) {
			$body.removeClass(function( index, classes ) {
				return ( classes.match( /(^|\s)billboard-\S+?-text-scheme/g ) || [] ).join( ' ' );
			});

			$body.addClass( 'billboard-' + value + '-text-scheme' );
		});
	});

	// Update CSS when properties are changed.
	_.each( cssSettings, function( settingKey ) {
		api( settingKey, function( setting ) {
			setting.bind(function( value ) {
				updateCSS();
			});
		});
	});

	function updateCSS() {
		var css = stylesTemplate({
			backgroundColor: api( 'billboard[background_color]' )(),
			backgroundImage: api( 'billboard[background_image]' )(),
			backgroundOverlayOpacity: api( 'billboard[background_overlay_opacity]' )() / 100,
			logoWidth: api( 'billboard[logo_width]' )()
		});

		$styles.html( css );
	}

	/**
	 * Prevent refresh loops in the Customizer.
	 *
	 * Nav menu locations in the theme don't exist in Billboard, so the
	 * Customizer attempts to force a fallback refresh, which ends up creating a
	 * refresh loop.
	 */
	api.MenusCustomizerPreview.onChangeNavMenuLocationsSetting = function() {};

})( jQuery, wp );
