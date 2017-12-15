/*jshint browserify:true, -W079 */

'use strict';

var VolumeSlider,
	$ = require( 'jquery' ),
	_ = require( 'underscore' ),
	Backbone = require( 'backbone' );

VolumeSlider = Backbone.View.extend({
	className: 'volume-slider',
	tagName: 'div',

	events: {
		'mousedown': 'activateSlider',
		'pointerdown' : 'activateSlider',
		'touchstart': 'activateSlider'
	},

	initialize: function( options ) {
		this.controller = options.controller;

		this.isHandleActive = false;

		this.listenTo( this.controller, 'change:volume', this.updateHandlePosition );

		_.bindAll( this, 'adjustVolume', 'deactivateSlider' );

		Backbone.$( window )
			.on( 'mouseup pointerup touchend', this.deactivateSlider )
			.on( 'mousemove pointermove touchmove', this.adjustVolume );
	},

	render: function() {
		this.$el.append( '<span class="volume-slider-handle" role="slider"></span>' );
		this.$handle = this.$el.find( '.volume-slider-handle' );
		this.updateHandlePosition();
		return this;
	},

	activateSlider: function( e ) {
		this.isHandleActive = true;
		this.outerHeightOffset = ( this.$el.outerHeight() - this.$el.height() ) / 2;

		// Update the handle position if the click is on the slider itself.
		if ( $( e.target ).hasClass( 'volume-slider' ) ) {
			this.controller.setVolume( 1 - ( e.pageY - ( this.$el.offset().top + this.outerHeightOffset ) ) / this.$el.height() );
		}
	},

	adjustVolume: function( e ) {
		var lowerBoundary, sliderHeight, upperBoundary;

		if ( ! this.isHandleActive ) {
			return;
		}

		sliderHeight = this.$el.height();
		upperBoundary = this.$el.offset().top + this.outerHeightOffset;
		lowerBoundary = upperBoundary + sliderHeight;

		if ( e.pageY - upperBoundary < 0 || e.pageY - lowerBoundary > 0 ) {
			return;
		}

		this.controller.setVolume( 1 - ( e.pageY - upperBoundary ) / sliderHeight );
	},

	deactivateSlider: function() {
		this.isHandleActive = false;
	},

	updateHandlePosition: function() {
		this.$handle.css( 'top', 100 - this.controller.get( 'volume' ) * 100 + '%' );
	}
});

module.exports = VolumeSlider;
