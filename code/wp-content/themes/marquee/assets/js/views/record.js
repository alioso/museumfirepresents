/*jshint browserify:true */
/*global mejs:false */

'use strict';

var Record,
	_ = require( 'underscore' ),
	Backbone = require( 'backbone' );

Record = Backbone.View.extend({
	events: {
		'click': 'click'
	},

	initialize: function( options ) {
		this.controller = options.controller;
		this.settings = options.settings;

		this.recordId = options.recordId || parseInt( this.$el.data( 'recordId' ), 10 );
		this.trackId = options.trackId || parseInt( this.$el.data( 'trackId' ), 10 );
		this.isTrack = this.trackId && ! _.isNaN( this.trackId );
		this.isSingle = options.isSingle || false;

		this.listenTo( this.controller, 'change:currentTime change:track', this.updateCurrentTime );
		this.listenTo( this.controller, 'change:duration change:track',    this.updateDuration );
		this.listenTo( this.controller, 'change:track change:status',      this.toggleState );
	},

	render: function() {
		this.$currentTime = this.$el.find( '.js-current-time' );
		this.$duration = this.$el.find( '.js-duration' );

		this.toggleState();
		this.updateCurrentTime();
		this.updateDuration();

		return this;
	},

	click: function( e ) {
		var model,
			$target = jQuery( e.target ),
			$forbidden = this.$el.find( 'a, .js-disable-playpause' );

		// Don't do anything if a link is clicked within the action element.
		if ( $target.is( $forbidden ) || !! $forbidden.find( $target ).length ) {
			return;
		}

		e.preventDefault();
		e.stopPropagation();

		if ( this.$el.hasClass( 'is-playing' ) ) {
			this.controller.pause();
			return;
		}

		if ( this.isTrack ) {
			model = this.controller.tracks.findWhere({ trackId: this.trackId });
		} else {
			model = this.controller.tracks.findWhere({ recordId: this.recordId });
		}

		if ( model ) {
			this.controller.setCurrentTrack( this.controller.tracks.indexOf( model ) );
			this.controller.play();
		} else {
			this.loadRecord();
		}
	},

	loadRecord: function() {
		var self = this,
			player = this.controller;

		jQuery.ajax({
			url: this.settings.ajaxUrl,
			type: 'GET',
			data: {
				action: 'marquee_get_record_data',
				record_id: this.recordId
			},
			dataType: 'json'
		}).done(function( response ) {
			var model;

			if ( ! response.success ) {
				return;
			}

			player.tracks.reset( response.data.tracks );

			if ( self.isTrack ) {
				model = player.tracks.findWhere({ trackId: self.trackId });
			} else {
				model = player.tracks.get( response.data.tracks[0].id );
			}

			if ( self.isSingle ) {
				player.tracks.reset( model );
			}

			player.setCurrentTrack( player.tracks.indexOf( model ) );
			player.play();
		});
	},

	toggleState: function() {
		var isPlaying = 'playing' === this.controller.get( 'status' ),
			currentRecordId = this.controller.currentTrack.get( 'recordId' ),
			currentTrackId = this.controller.currentTrack.get( 'trackId' );

		if ( this.isTrack ) {
			this.$el.toggleClass( 'is-playing', isPlaying && this.trackId === currentTrackId );
		} else {
			this.$el.toggleClass( 'is-playing', isPlaying && this.recordId === currentRecordId );
		}
	},

	updateCurrentTime: function() {
		var currentTime = this.controller.get( 'currentTime' ),
			currentTimeCode = mejs.Utility.secondsToTimeCode( currentTime, false ),
			currentTrackId = this.controller.currentTrack.get( 'trackId' );

		if ( this.$currentTime && this.trackId === currentTrackId ) {
			this.$currentTime.text( currentTimeCode );
		}
	},

	updateDuration: function() {
		var durationTimeCode = mejs.Utility.secondsToTimeCode( this.controller.get( 'duration' ), false ),
			currentTrackId = this.controller.currentTrack.get( 'trackId' );

		if ( this.$duration && this.trackId === currentTrackId ) {
			this.$duration.text( durationTimeCode );
		}
	}
});

module.exports = Record;
