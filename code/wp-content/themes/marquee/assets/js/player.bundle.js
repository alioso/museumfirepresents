require=(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
(function (global){
/*global _marqueePlayerSettings, JSON:false */
/*jshint browserify:true, -W079 */

'use strict';

var currentTrackDetails, playPauseButton, player, playerView, tracks,
	$ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	app = require( 'app' )( 'marquee' ),
	$body = $( 'body' ),
	data = JSON.parse( $( '#site-player-settings' ).html() ),
	settings = app.settings( _marqueePlayerSettings );

app.views.CurrentTrackDetails = require( './views/player/current-track-details' );
app.views.PlayPauseButton     = require( './views/player/play-pause-button' );
app.views.Player              = require( './views/player/player' );
app.views.Playlist            = require( './views/player/playlist' );
app.views.Track               = require( './views/player/track' );
app.views.VolumePanel         = require( './views/player/volume-panel' );
app.views.VolumeSlider        = require( './views/player/volume-slider' );

tracks = new app.collections.Tracks( data.tracks );

new app.views.Panel({
	el: document.getElementById( 'site-player-panel' ),
	controller: app.playerPanel,
	closeOn: 'swiperight'
});

new app.views.PanelToggleButton({
	controller: app.playerPanel,
	el: document.getElementById( 'site-player-toggle' )
});

player = app.players.marquee = new app.controllers.Player({}, {
	id: 'site-player',
	mejsPluginPath: settings.mejs.pluginPath,
	persist: true,
	players: app.players,
	signature: data.signature,
	tracks: tracks
});

player.fetch();

// Restore the original playlist if all tracks are removed.
player.listenTo( player.tracks, 'remove', function() {
	if ( this.tracks.length < 1 ) {
		this.tracks.add( data.tracks );
	}
});

currentTrackDetails = new app.views.CurrentTrackDetails({
	controller: player
});

playPauseButton = new app.views.PlayPauseButton({
	controller: player,
	l10n: app.l10n
});

playerView = new app.views.Player({
	controller: player,
	l10n: app.l10n
});

$( '#site-player-panel' ).append( playerView.render().$el );
$body.append( currentTrackDetails.render().$el );
$body.append( playPauseButton.render().$el );

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./views/player/current-track-details":2,"./views/player/play-pause-button":3,"./views/player/player":4,"./views/player/playlist":5,"./views/player/track":6,"./views/player/volume-panel":7,"./views/player/volume-slider":8,"app":"app"}],2:[function(require,module,exports){
(function (global){
/*jshint browserify:true */
/*global mejs:false */

'use strict';

var CurrentTrackDetails,
	app = require( 'app' )( 'marquee' ),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

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

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"app":"app"}],3:[function(require,module,exports){
(function (global){
/*jshint browserify:true */

'use strict';

var PlayPauseButton,
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

PlayPauseButton = Backbone.View.extend({
	className: 'site-play-pause-button',
	tagName: 'button',

	events: {
		'click': 'togglePlayback'
	},

	initialize: function( options ) {
		this.controller = options.controller;
		this.l10n = options.l10n;

		this.listenTo( this.controller, 'change:status', this.updateStatus );
	},

	render: function() {
		this.updateStatus();
		return this;
	},

	togglePlayback: function() {
		if ( 'playing' === this.controller.get( 'status' ) ) {
			this.controller.pause();
		} else {
			this.controller.play();
		}
	},

	updateStatus: function() {
		var isPlaying = 'playing' === this.controller.get( 'status' );

		this.$el
			.text( isPlaying ? this.l10n.pause : this.l10n.play )
			.toggleClass( 'play', ! isPlaying )
			.toggleClass( 'pause', isPlaying );

	}
});

module.exports = PlayPauseButton;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],4:[function(require,module,exports){
(function (global){
/*jshint browserify:true */
/*global mejs:false */

'use strict';

var Player,
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	app = require( 'app' )( 'marquee' ),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null),
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

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./playlist":5,"./volume-panel":7,"app":"app"}],5:[function(require,module,exports){
(function (global){
/*jshint browserify:true */

'use strict';

var Playlist,
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null),
	Track = require( './track' );

Playlist = Backbone.View.extend({
	className: 'playlist',
	tagName: 'div',

	initialize: function( options ) {
		this.controller = options.controller;

		this.listenTo( this.controller.tracks, 'add', this.addTrack );
	},

	render: function() {
		this.$el.empty().append( '<ol class="tracks-list"></ol>' );
		this.$tracksList = this.$el.find( '.tracks-list' );
		this.controller.tracks.each( this.addTrack, this );
		return this;
	},

	addTrack: function( track ) {
		this.$tracksList.append(
			new Track({
				model: track,
				controller: this.controller
			}).render().el
		);
	}
});

module.exports = Playlist;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./track":6}],6:[function(require,module,exports){
(function (global){
/*jshint browserify:true */

'use strict';

var Track,
	app = require( 'app' )( 'marquee' ),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

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

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"app":"app"}],7:[function(require,module,exports){
(function (global){
/*jshint browserify:true */

'use strict';

var VolumePanel,
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null),
	VolumeSlider = require( './volume-slider' );

VolumePanel = Backbone.View.extend({
	className: 'volume-panel',
	tagName: 'div',

	events: {
		'click .volume-toggle': 'toggleMute'
	},

	initialize: function( options ) {
		this.parent = options.parent;
		this.controller = options.controller;

		this.l10n = _.extend({
			'mute': 'Mute',
			'unmute': 'Unmute'
		}, options.l10n );

		this.listenTo( this.controller, 'change:volume', this.updateClasses );
	},

	render: function() {
		this.$el.append( '<button class="volume-toggle">' + this.l10n.mute + '</button>' );
		this.$toggleButton = this.$el.find( '.volume-toggle' );

		this.$el.append(
			new VolumeSlider({
				parent: this,
				controller: this.controller,
				playerView: this.parent.parent
			}).render().el
		);
		return this;
	},

	toggleMute: function() {
		var volume = this.controller.get( 'volume' ) < 0.05 ? 0.8 : 0;
		this.controller.setVolume( volume );
	},

	updateClasses: function() {
		var isMuted = this.controller.get( 'volume' ) < 0.05;
		this.parent.$el.toggleClass( 'is-muted', isMuted );
		this.$toggleButton.text( isMuted ? this.l10n.unmute : this.l10n.mute )
			.toggleClass( 'is-muted', isMuted );
	}
});

module.exports = VolumePanel;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./volume-slider":8}],8:[function(require,module,exports){
(function (global){
/*jshint browserify:true, -W079 */

'use strict';

var VolumeSlider,
	$ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

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

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],"app":[function(require,module,exports){
(function (global){
/*jshint browserify:true */

'use strict';

var _ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null);

function Application() {
	var settings = {};

	_.extend( this, {
		controllers: {},
		collections: {},
		l10n: {},
		models: {},
		players: {},
		routers: {},
		views: {}
	});

	this.settings = function( options ) {
		if ( options ) {
			_.extend( settings, options );
		}

		if ( settings.l10n ) {
			this.l10n = _.extend( this.l10n, settings.l10n );
			delete settings.l10n;
		}

		return settings || {};
	};
}

module.exports = function( name ) {
	global[ name ] = global[ name ] || new Application();
	return global[ name ];
};

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}]},{},[1]);
