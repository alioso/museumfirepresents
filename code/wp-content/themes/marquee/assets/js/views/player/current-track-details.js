/*jshint browserify:true */
/*global mejs:false */

'use strict';

var CurrentTrackDetails,
	app = require( 'app' )( 'marquee' ),
	Backbone = require( 'backbone' );

CurrentTrackDetails = Backbone.View.extend({
	className: 'site-current-track-details',
	tagName: 'div',
	template: app.template( 'site-current-track-details' ),

	initialize: function( options ) {
		this.controller = options.controller;

		this.listenTo( this.controller, 'change:currentTime', this.updateCurrentTime );
		this.listenTo( this.controller, 'change:track',       this.updateDetails );
		this.listenTo( this.controller, 'change:duration',    this.updateDuration );
	},

	render: function() {
		var track = this.controller.currentTrack.toJSON();

		track.artist = this.getCurrentArtist();

		this.$el.html( this.template( track ) );

		this.$artist      = this.$el.find( '.artist' );
		this.$currentTime = this.$el.find( '.current-time' );
		this.$duration    = this.$el.find( '.duration' );
		this.$title       = this.$el.find( '.title' );

		this.updateCurrentTime();
		this.updateDetails( this.controller.currentTrack );
		this.updateDuration();

		return this;
	},

	getCurrentArtist: function() {
		var track = this.controller.currentTrack;
		return track.get( 'artist' ) || track.get( 'meta' ).artist || '';
	},

	updateCurrentTime: function() {
		var currentTime = this.controller.get( 'currentTime' ),
			currentTimeCode = mejs.Utility.secondsToTimeCode( currentTime, false );

		this.$currentTime.text( currentTimeCode );
	},

	updateDetails: function() {
		this.$artist.text( this.getCurrentArtist() );
		this.$title.text( this.controller.currentTrack.get( 'title' ) );
	},

	updateDuration: function() {
		var durationTimeCode = mejs.Utility.secondsToTimeCode( this.controller.get( 'duration' ), false );
		this.$duration.text( durationTimeCode );
	}
});

module.exports = CurrentTrackDetails;
