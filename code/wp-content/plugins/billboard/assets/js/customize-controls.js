/*global _:false, _billboardControlsSettings:false, tinyMCE:false, wp:false */

(function( $, _, wp, undefined ) {
	'use strict';

	var api = wp.customize,
		settings = _billboardControlsSettings;

	/**
	 * @link http://24ways.org/2010/calculating-color-contrast/
	 */
	function getContrastScheme( hexcolor ) {
		var r, g, b, yiq;

		hexcolor = 0 === hexcolor.indexOf( '#' ) ? hexcolor.substr( 1 ) : hexcolor;
		r = parseInt( hexcolor.substr( 0, 2 ), 16 );
		g = parseInt( hexcolor.substr( 2, 2 ), 16 );
		b = parseInt( hexcolor.substr( 4, 2 ), 16 );
		yiq = ( ( r * 299 ) + ( g * 587 ) + ( b * 114 ) ) / 1000;

		return ( 128 <= yiq ) ? 'dark' : 'light';
	}

	/**
	 * View the Billboard in the previewer when opening the Billboard panel.
	 */
	api.panel( 'billboard', function( panel ) {
		var preBillboardUrl;

		panel.expanded.bind(function( isOpen ) {
			if ( isOpen ) {
				preBillboardUrl = api.previewer.previewUrl();
				api.previewer.previewUrl( settings.previewUrl );
			} else if ( preBillboardUrl ) {
				api.previewer.previewUrl( preBillboardUrl );
				preBillboardUrl = null;
			}
		});
	});

	/**
	 * Toggle the visibility of the background color opacity control depending
	 * on whether or not a background image is set.
	 */
	api( 'billboard[background_image]', function( setting ) {
		api.control( 'billboard_background_overlay_opacity', function( control ) {
			var toggleVisibility = function( value ) {
				control.container.toggle( '' !== value );
			};

			toggleVisibility( setting() );
			setting.bind( toggleVisibility );
		});
	});

	/**
	 * Toggle the maintenance mode control depending on whether or not a
	 * visibility mode has been set.
	 */
	api( 'billboard[mode]', function( setting ) {
		api.control( 'billboard_maintenance_mode', function( control ) {
			var toggleVisibility = function( value ) {
				control.container.toggle( '' !== value );
			};

			toggleVisibility( setting() );
			setting.bind( toggleVisibility );
		});
	});

	/**
	 * Update the text scheme setting to contrast the background color.
	 */
	api( 'billboard[background_color]', function( setting ) {
		setting.bind(function( value ) {
			var scheme = getContrastScheme( value );
			api( 'billboard[text_scheme]' ).set( scheme );
		});
	});

	/**
	 * Editor control.
	 */
	api.controlConstructor['billboard-editor'] = api.Control.extend({
		editor: null,
		textarea: null,

		ready: function() {
			var control = this,
				section = api.section( this.section() );

			_.bindAll( this, 'onTextEditorChange', 'onVisualEditorChange' );

			this.$editorPane = $( '#customize-billboard-editor-pane_' + this.id );

			this.container.on( 'click', '.js-toggle-editor', function( e ) {
				e.preventDefault();
				control.toggleEditor();
			});

			section.expanded.bind(function( isOpen ) {
				if ( ! isOpen ) {
					control.hideEditor();
				}
			});
		},

		focus: function() {
			this.showEditor();
			api.Control.prototype.focus.apply( this );
		},

		hideEditor: function() {
			this.$editorPane.removeClass( 'is-active' );

			if ( this.editor && this.textarea ) {
				this.editor.off( 'input change keyup', this.onVisualEditorChange );
				this.textarea.off( 'input', this.onTextEditorChange );
			}
		},

		showEditor: function() {
			this.$editorPane.addClass( 'is-active' );

			this.editor = tinyMCE.get( this.id );
			this.textarea = $( document.getElementById( this.id ) );

			this.editor.on( 'input change keyup', _.debounce( this.onVisualEditorChange, 1500 ) );
			this.textarea.on( 'input', _.debounce( this.onTextEditorChange, 1500 ) );
		},

		toggleEditor: function() {
			if ( this.$editorPane.hasClass( 'is-active' ) ) {
				this.hideEditor();
			} else {
				this.showEditor();
			}
		},

		onVisualEditorChange: function() {
			var value = wp.editor.removep( this.editor.getContent() );
			this.setting.set( value );
		},

		onTextEditorChange: function() {
			this.setting.set( this.textarea.val() );
		}
	});

})( jQuery, _, wp );
