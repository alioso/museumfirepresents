/*jshint browserify:true */
/*global mejs:false */

'use strict';

var Player,
	_ = require( 'underscore' ),
	app = require( 'app' )( 'marquee' ),
	Backbone = require( 'backbone' ),
	Playlist = require( './playlist' ),
	VolumePanel = require( './volume-panel' );

Player = Backbone.View.extend({
	className: 'site-player',
	tagName: 'div',
	template: app.template( 'site-player' ),

	events: {
		'click .next':         'nextTrack',
		'click .play-pause':   'togglePlayback',
		'click .previous':     'previousTrack',
		'click .progress-bar': 'seek',
		'click .repeat':       'toggleRepeatStatus',
		'click .shuffle':      'toggleShuffle',
		'click .volume-bar':   'changeVolume'
	},

	initialize: function( options ) {
		this.controller = options.controller;

		this.l10n = _.extend({
			'disableRepeat': 'Disable Repeat',
			'disableShuffle': 'Disable Shuffle',
			'mute': 'Mute',
			'pause': 'Pause',
			'play': 'Play',
			'repeat': 'Repeat',
			'shuffle': 'Shuffle',
			'unmute': 'Unmute'
		}, options.l10n );

		this.listenTo( this.controller,        'change:currentTime', this.updateProgress );
		this.listenTo( this.controller,        'change:duration',    this.updateDuration );
		this.listenTo( this.controller,        'change:repeat',      this.updateRepeatStatus );
		this.listenTo( this.controller,        'change:shuffle',     this.updateShuffle );
		this.listenTo( this.controller,        'change:status',      this.updateStatus );
		this.listenTo( this.controller.tracks, 'add remove reset',   this.updateTracksCount );
	},

	render: function() {
		this.$el.html( this.template( this.controller.currentTrack.toJSON() ) );

		this.$controls    = this.$el.find( '.controls' );
		this.$currentTime = this.$el.find( '.current-time' );
		this.$duration    = this.$el.find( '.duration' );
		this.$playBar     = this.$el.find( '.play-bar' );
		this.$playPause   = this.$el.find( '.play-pause' );
		this.$progressBar = this.$el.find( '.progress-bar' );
		this.$repeat      = this.$el.find( '.repeat' );
		this.$shuffle     = this.$el.find( '.shuffle' );

		// Don't show volume control on Android or iOS.
		if ( ! mejs.MediaFeatures.isAndroid && ! mejs.MediaFeatures.isiOS ) {
			this.$controls.append(
				new VolumePanel({
					l10n: this.l10n,
					parent: this,
					controller: this.controller
				}).render().el
			);
		}

		this.$el.append(
			new Playlist({
				parent: this,
				controller: this.controller
			}).render().el
		);

		this.updateProgress();
		this.updateRepeatStatus();
		this.updateShuffle();
		this.updateStatus();
		this.updateTracksCount();

		return this;
	},

	changeVolume: function( e ) {
		var position = e.pageX - this.$volumeBar.offset().left,
			percent = position / this.$volumeBar.outerWidth();

		this.controller.setVolume( Number( ( percent ).toFixed( 2 ) ) );
	},

	nextTrack: function( e ) {
		this.controller.log({ action: 'skip' });
		this.controller.nextTrack();
		this.controller.play();
	},

	previousTrack: function( e ) {
		this.controller.previousTrack();
		this.controller.play();
	},

	seek: function( e ) {
		var duration = this.controller.get( 'duration' ),
			position = e.pageX - this.$progressBar.offset().left,
			percent = position / this.$progressBar.outerWidth();

		percent = percent < 0.05 ? 0 : percent;
		this.controller.setCurrentTime( percent * duration );
	},

	togglePlayback: function() {
		if ( 'playing' === this.controller.get( 'status' ) ) {
			this.controller.pause();
		} else {
			this.controller.play();
		}
	},

	toggleRepeatStatus: function() {
		if ( this.controller.get( 'repeat' ) ) {
			this.controller.set( 'repeat', false );
		} else {
			this.controller.set( 'repeat', true );
		}
	},

	toggleShuffle: function() {
		if ( this.controller.get( 'shuffle' ) ) {
			this.controller.set( 'shuffle', false );
		} else {
			this.controller.set( 'shuffle', true );
		}
	},

	updateDuration: function() {
		var durationTimeCode = mejs.Utility.secondsToTimeCode( this.controller.get( 'duration' ), false );
		this.$duration.text( durationTimeCode );
	},

	updateProgress: function() {
		var currentTime = this.controller.get( 'currentTime' ),
			currentTimeCode = mejs.Utility.secondsToTimeCode( currentTime, false );

		this.$currentTime.text( currentTimeCode );
		this.$playBar.width( this.controller.getProgress() * 100 + '%' );
	},

	updateRepeatStatus: function() {
		var isActive = this.controller.get( 'repeat' ),
			text = isActive ? this.l10n.disableRepeat : this.l10n.repeat;

		this.$el.toggleClass( 'is-repeating', isActive );
		this.$repeat.text( text )
			.attr( 'title', text )
			.toggleClass( 'is-active', isActive );
	},

	updateShuffle: function() {
		var isActive = this.controller.get( 'shuffle' ),
			text = isActive ? this.l10n.disableShuffle : this.l10n.shuffle;

		this.$el.toggleClass( 'is-shuffling', isActive );
		this.$shuffle.text( text )
			.attr( 'title', text )
			.toggleClass( 'is-active', isActive );
	},

	updateStatus: function() {
		var isPlaying = 'playing' === this.controller.get( 'status' );
		this.$el.toggleClass( 'is-playing', isPlaying );
		this.$playPause.text( isPlaying ? this.l10n.pause : this.l10n.play )
					   .toggleClass( 'play', ! isPlaying )
					   .toggleClass( 'pause', isPlaying );
	},

	updateTracksCount: function() {
		this.$el.removeClass(function( index, classes ) {
			return ( classes.match( /\s?tracks-count-\d+/g ) || [] ).join( ' ' );
		}).addClass( 'tracks-count-' + this.controller.tracks.length );
	}
});

module.exports = Player;
