/*global _:false, _billboardFontsPreviewSettings:false, Backbone:false, WebFont:false, wp:false */

(function( window, $, _, Backbone, wp, undefined ) {
	'use strict';

	var api = wp.customize,
		app = {},
		loadedFonts = [],
		settings = _billboardFontsPreviewSettings;

	_.extend( app, { model: {}, view: {} } );

	/**
	 * ========================================================================
	 * MODELS
	 * ========================================================================
	 */

	app.model.Group = Backbone.Model.extend({
		defaults: {
			id: '',
			family: '',
			selector: '',
			service: 'google',
			size: '',
			stack: '',
			variations: ''
		},

		initialize: function() {
			this.listenTo( this, 'change:family', this.loadFont );
		},

		loadFont: function() {
			var config = {
					events: false,
					classes: false
				},
				family = this.get( 'family' ),
				service = this.get( 'service' ),
				typekitKitId = api( 'billboard_fonts[typekit_id]' )();

			if ( 'google' === service && '' !== family ) {
				config.google = { families: [ this.getGoogleFamilyDefinition() ] };
			}

			if ( 'typekit' === service && '' !== typekitKitId ) {
				config.typekit = { id: typekitKitId };
			}

			app.loadFont( config );
		},

		getGoogleFamilyDefinition: function() {
			var family = this.get( 'family' ),
				variations = this.get( 'variations' );

			if ( '' !== variations ) {
				family += ':' + variations;
			}

			if ( '' !== settings.subsets && 'latin' !== settings.subsets ) {
				family += ':' + settings.subsets;
			}

			return family;
		}
	});

	/**
	 * ========================================================================
	 * VIEWS
	 * ========================================================================
	 */

	app.view.Style = wp.Backbone.View.extend({
		tagName: 'style',

		initialize: function() {
			this.model = this.options.model;
			this.listenTo( this.model, 'change:size change:stack', this.render );
		},

		render: function() {
			var css = '',
				size = this.model.get( 'size' ),
				stack = this.model.get( 'stack' ),
				selector = this.model.get( 'selector' );

			css = selector + ' {';

			if ( '' !== stack ) {
				css += ' font-family: ' + stack + ';';
			}

			if ( '' !== size ) {
				//css += ' font-size: ' + parseInt( size, 10 ) + 'px;';
			}

			css += '}';

			this.$el.html( css );
			return this;
		}
	});

	/**
	 * ========================================================================
	 * SETUP
	 * ========================================================================
	 */

	app.loadFont = function( config ) {
		var configJson = JSON.stringify( config );

		// Load new fonts only.
		if ( config && -1 === loadedFonts.indexOf( configJson ) ) {
			WebFont.load( config );
			loadedFonts.push( configJson );
		}
	};

	api.bind( 'preview-ready', function() {
		var $head = $( 'head' );

		_.each( settings.groups, function( group ) {
			var style,
				model = new app.model.Group( group ),
				value = api( 'billboard_fonts[' + group.id + '_font]' )();

			// Use saved font properties from the corresponding setting.
			if ( value ) {
				model.set( value );
			}

			style = new app.view.Style({
				model: model
			});

			$head.append( style.render().$el );

			api( 'billboard_fonts[' + group.id + '_font]', function( setting ) {
				setting.bind(function( value ) {
					model.set( value );
				});
			});
		});
	});

})( window, jQuery, _, Backbone, wp );
