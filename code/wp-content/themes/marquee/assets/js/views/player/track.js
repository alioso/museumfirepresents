/*jshint browserify:true */

'use strict';

var Track,
	app = require( 'app' )( 'marquee' ),
	Backbone = require( 'backbone' );

Track = Backbone.View.extend({
	className: 'track',
	tagName: 'li',
	template: app.template( 'site-player-track' ),

	events: {
		'click': 'play',
		'click .js-remove': 'destroy'
	},

	initialize: function( options ) {
		this.controller = options.controller;

		this.listenTo( this.model, 'change:status', this.updateStatus );
		this.listenTo( this.model, 'destroy', this.remove );
		this.listenTo( this.controller, 'change:track', this.updateCurrent );
		this.listenTo( this.controller, 'change:track change:status', this.updateStatus );
	},

	render: function() {
		this.$el.html( this.template( this.model.toJSON() ) );
		this.updateCurrent();
		this.updateStatus();
		return this;
	},

	play: function( e ) {
		var $target = jQuery( e.target ),
			$forbidden = this.$el.find( 'a, .js-remove' ),
			index = this.controller.tracks.indexOf( this.model );

		// Don't do anything if a link is clicked within the action element.
		if ( $target.is( $forbidden ) || !! $forbidden.find( $target ).length ) {
			return;
		}

		this.controller.setCurrentTrack( index ).play();
	},

	destroy: function( e ) {
		e.preventDefault();
		this.model.trigger( 'destroy', this.model );
	},

	remove: function() {
		this.$el.remove();
	},

	updateCurrent: function() {
		this.$el.toggleClass( 'is-current', this.controller.currentTrack.get( 'id' ) === this.model.get( 'id' ) );
	},

	updateStatus: function() {
		var isPlaying = 'playing' === this.controller.get( 'status' );
		this.$el.toggleClass( 'is-playing', isPlaying && this.controller.currentTrack === this.model );
		this.$el.toggleClass( 'is-error', 'error' === this.model.get( 'status' ) );
	}
});

module.exports = Track;
