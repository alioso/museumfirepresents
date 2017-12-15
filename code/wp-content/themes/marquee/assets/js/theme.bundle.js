require=(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
if (typeof Object.create === 'function') {
  // implementation from standard node.js 'util' module
  module.exports = function inherits(ctor, superCtor) {
    ctor.super_ = superCtor
    ctor.prototype = Object.create(superCtor.prototype, {
      constructor: {
        value: ctor,
        enumerable: false,
        writable: true,
        configurable: true
      }
    });
  };
} else {
  // old school shim for old browsers
  module.exports = function inherits(ctor, superCtor) {
    ctor.super_ = superCtor
    var TempCtor = function () {}
    TempCtor.prototype = superCtor.prototype
    ctor.prototype = new TempCtor()
    ctor.prototype.constructor = ctor
  }
}

},{}],2:[function(require,module,exports){
(function (global){
/*jshint browserify:true */
/*global JSON:false */

'use strict';

var Tracks,
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	app = require( 'app' )( 'marquee' ),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null),
	Track = require( '../models/track' );

Tracks = Backbone.Collection.extend({
	model: Track,

	initialize: function( models, options ) {
		this.options = _.extend({
			id: 'site-player-tracks'
		}, options );
	},

	fetch: function() {
		var tracks;

		if ( app.hasSessionStorage() ) {
			tracks = JSON.parse( sessionStorage.getItem( this.options.id ) );

			if ( null !== tracks ) {
				this.reset( tracks );
			}
		}
	},

	save: function() {
		sessionStorage.setItem( this.options.id, JSON.stringify( this.toJSON() ) );
	}
});

module.exports = Tracks;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"../models/track":6,"app":"app"}],3:[function(require,module,exports){
(function (global){
/*jshint browserify:true */

'use strict';

var Page,
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null),
	$document = Backbone.$( document ),
	$window = Backbone.$( window );

Page = Backbone.Model.extend({
	defaults: {
		bodyClasses: '',
		isLoading: false,
		loadedScripts: [],
		loadedStyles: [],
		mode: 'initial',
		state: {},
		url: ''
	},

	initialize: function() {
		var page = this;

		this.pageStateCache = {};

		_.bindAll( this, 'updateUrl' );
		$document.ready(function() {
			page.updateUrl();
		});

		_.bindAll( this, 'updateMode' );
		$window.on( 'load orientationchange resize', _.throttle( this.updateMode, 100 ) );
	},

	/**
	 * Cache the page state.
	 */
	cacheState: function( state ) {
		this.pageStateCache[ document.location.pathname ] = state;
	},

	/**
	 * Retrieve cached state data.
	 */
	getCachedState: function() {
		return this.pageStateCache[ document.location.pathname ] || {};
	},

	/**
	 * Retrieve the viewport width.
	 */
	getViewportWidth: function() {
		return window.innerWidth || $window.width();
	},

	/**
	 * Update the responsive state.
	 */
	updateMode: function() {
		var mode,
			vw = this.getViewportWidth();

		if ( vw < 783 ) {
			mode = 'mobile';
		} else if ( vw < 1024 ) {
			mode = 'tablet';
		} else {
			mode = 'desktop';
		}

		this.set( 'mode', mode );
	},

	/**
	 * Update the current URL.
	 */
	updateUrl: function() {
		this.set( 'url', window.location.href );
	}
});

module.exports = Page;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],4:[function(require,module,exports){
(function (global){
/*jshint browserify:true */
/*global JSON:false, MediaElement:false, mejs:false */

'use strict';

var mejsCreateErrorMessage, Player,
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	app = require( 'app' )( 'marquee' ),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null),
	Tracks = require( '../collections/tracks' );

Player = Backbone.Model.extend({
	defaults: {
		currentTime: 0,
		currentTrackIndex: 0,
		duration: 0,
		loop: true,
		repeat: false,
		shuffle: false,
		status: '',
		volume: 0.8
	},

	initialize: function( attributes, options ) {
		var self = this;

		this.options = _.extend({
			id: 'player',
			mejsPluginPath: null,
			persist: false,
			players: {},
			tracks: null
		}, options );

		this.autoResumed = false;
		this.currentTimeIntervalId = null;
		this.currentTrack = null;
		this.initialCurrentTime = 0;
		this.listened = null;
		this.media = null;
		this.mediaCanPlay = false;
		this.playIntervalId = null;

		this.tracks = this.options.tracks || new Tracks([{}]);
		delete this.options.tracks;

		// Remember the player state between requests.
		if ( this.options.persist ) {
			this.fetchTracks();
			this.on( 'change', this.save, this );
			this.tracks.on( 'add remove reset', this.tracks.save, this.tracks );
		}

		// Reset the current track index when replacing tracks.
		this.tracks.on( 'reset', function() {
			this.set( 'currentTrackIndex', null );
		}, this );

		_.bindAll(
			this,
			'bindAudio',
			'onMediaCanPlay',
			'onMediaEnded',
			'onMediaError',
			'onMediaLoadedMetadata',
			'onMediaPause',
			'onMediaPlaying',
			'onMediaTimeUpdate',
			'onMediaVolumeChange',
			'onUserGesture'
		);

		if ( mejs.MediaFeatures.isAndroid || mejs.MediaFeatures.isiOS ) {
			Backbone.$( window ).one( 'keydown.marqueePlayer mousedown.marqueePlayer pointerdown.marqueePlayer touchend.marqueePlayer', this.onUserGesture );
		}

		// Proxy the createErrorMessage method to prevent issues since the media element isn't in the DOM.
		// @todo This may have been fixed in MediaElement.js.
		if ( ! mejsCreateErrorMessage ) {
			mejsCreateErrorMessage = mejs.HtmlMediaElementShim.createErrorMessage;
		}

		mejs.HtmlMediaElementShim.createErrorMessage = function( playback, options, poster ) {
			if ( 'playerId' in options && options.playerId === self.options.id ) {
				self.set( 'status', 'error' );

				if ( self.currentTrack ) {
					self.currentTrack.set( 'status', 'error' );
				}

				//self.nextTrack();
				return;
			}

			mejsCreateErrorMessage.apply( this, arguments );
		};

		return this;
	},

	// http://blog.foolip.org/2014/02/10/media-playback-restrictions-in-blink/
	onUserGesture: function() {
		if ( ! this.media || ! ( 'load' in this.media ) ) {
			return;
		}

		this.media.load();
		this.setCurrentTime( this.initialCurrentTime );
	},

	getProgress: function() {
		var currentTime = this.get( 'currentTime' ),
			duration = this.get( 'duration' );

		return currentTime / duration;
	},

	loadTrack: function( track ) {
		if ( 'undefined' === typeof this.mediaNode ) {
			this.mediaNode = document.createElement( 'audio' );
		}

		// @todo Should this ever be set to 'loaded'?
		if ( this.currentTrack ) {
			this.currentTrack.set( 'status', 'error' === this.currentTrack.get( 'status') ? 'error' : 'paused' );
		}

		// Reload the audio object if the type has changed.
		if ( null !== this.media && track.get( 'type' ) !== this.currentTrack.get( 'type' ) ) {
			if ( this.media && ( 'pause' in this.media ) ) {
				this.media.pause();
				this.media.remove();
			}

			this.media = null;
		}

		// Update the current track.
		this.set( 'status', 'loading' );
		this.currentTrack = track;
		this.currentTrack.set( 'status', 'loading' );

		// Instantiate MediaElement.js.
		if ( null === this.media ) {
			this.mediaCanPlay = false;

			this.mediaNode.setAttribute( 'preload', 'auto' );
			this.mediaNode.setAttribute( 'src', track.get( 'src' ) );
			this.mediaNode.setAttribute( 'type', track.get( 'type' ) );

			try {
				this.media = new MediaElement( this.mediaNode, {
					playerId: this.options.id,
					pluginPath: this.options.mejsPluginPath || mejs.Utility.getScriptPath([ 'mediaelement.js','mediaelement.min.js','mediaelement-and-player.js','mediaelement-and-player.min.js' ]),
					startVolume: this.get( 'volume' ),
					success: this.bindAudio
				});
			} catch ( ex ) {
				this.media = null;
			}
		}

		// Load a new source using the existing audio object.
		else {
			this.media.pause();
			this.media.setSrc( track.get( 'src' ) );
			this.media.load();
			//this.media.setCurrentTime( 0 );
		}

		this.trigger( 'change:track', track );
	},

	nextTrack: function() {
		var nextIndex,
			currentIndex = this.get( 'currentTrackIndex' );

		if ( this.get( 'shuffle' ) ) {
			nextIndex = Math.floor( Math.random() * this.tracks.length );
		} else {
			nextIndex = currentIndex + 1 >= this.tracks.length ? 0 : currentIndex + 1;
		}

		this.setCurrentTrack( nextIndex );
	},

	pause: function() {
		clearInterval( this.playIntervalId );
		this.media.pause();
	},

	pauseOtherPlayers: function() {
		var self = this;

		// Pause MediaElement.js generated players.
		_.each( mejs.players, function( player ) {
			player.pause();
		});

		_.each( this.options.players, function( player ) {
			if ( self.options.id !== player.options.id && 'playing' === player.get( 'status' ) ) {
				player.pause();
			}
		});
	},

	play: function() {
		var player = this;

		// Hack to work around issues with playing before the media is ready.
		clearInterval( this.playIntervalId );
		this.pauseOtherPlayers();

		this.playIntervalId = setInterval(function() {
			if ( player.mediaCanPlay && null === player.currentTimeIntervalId ) {
				if ( 'play' in player.media ) {
					player.media.play();

					// Log the play event.
					if ( ! player.autoResumed && player.get( 'currentTime' ) < 2 ) {
						player.log({ action: 'play', time: 0 });
					}
				}

				player.autoResumed = false;
				clearInterval( player.playIntervalId );
			}
		}, 50 );
	},

	previousTrack: function() {
		var currentIndex = this.get( 'currentTrackIndex' ),
			previousIndex = currentIndex - 1 < 0 ? this.tracks.length - 1 : currentIndex - 1;

		this.setCurrentTrack( previousIndex );
	},

	// @todo Set up some sort of queue instead?
	// @todo May need to account for buffered time on iOS
	// @todo https://github.com/audiotheme/jquery-cue/blob/19909bfe7eb70c382593347a9277aa8c685ba77f/src/feature-history.js#L82
	setCurrentTime: function( time ) {
		var player = this;

		clearInterval( this.currentTimeIntervalId );

		this.currentTimeIntervalId = setInterval(function() {
			if ( 4 === player.media.readyState ) { //  && ( ! mejs.MediaFeatures.isWebkit || time < player.media.buffered.end( 0 ) )
				player.media.setCurrentTime( time );
				clearInterval( player.currentTimeIntervalId );
				player.currentTimeIntervalId = null;
			}
		}, 50 );
	},

	setCurrentTrack: function( index ) {
		while ( index >= this.tracks.length ) {
			index--;
		}

		if ( index !== this.get( 'currentTrackIndex' ) || null === this.currentTrack ) {
			this.set( 'currentTrackIndex', index );
			this.loadTrack( this.tracks.at( index ) );
		}

		return this;
	},

	setVolume: function( volume ) {
		this.media.volume = volume;
	},

	stop: function() {
		this.media.pause();
	},

	bindAudio: function( audio ) {
		// @todo Do these get blown away when removing an audio node?
		audio.addEventListener( 'canplay',        this.onMediaCanPlay );
		audio.addEventListener( 'loadedmetadata', this.onMediaCanPlay );
		audio.addEventListener( 'ended',          this.onMediaEnded );
		audio.addEventListener( 'loadedmetadata', this.onMediaLoadedMetadata );
		audio.addEventListener( 'error',          this.onMediaError );
		// abort, emptied, stalled, suspend
		audio.addEventListener( 'pause',          this.onMediaPause );
		audio.addEventListener( 'playing',        this.onMediaPlaying );
		audio.addEventListener( 'loadedmetadata', this.onMediaTimeUpdate );
		audio.addEventListener( 'timeupdate',     this.onMediaTimeUpdate );
		audio.addEventListener( 'volumechange',   this.onMediaVolumeChange );
	},

	onMediaCanPlay: function() {
		this.mediaCanPlay = true;
	},

	onMediaTimeUpdate: function() {
		var threshold = this.media.duration * 0.2;

		this.set( 'currentTime', this.media.currentTime );

		// Log a listen event when the current time passes the threshold.
		if ( this.listened && this.listened < threshold && this.media.currentTime > threshold ) {
			this.log({ action: 'listen' });
		}

		this.listened = this.media.currentTime;
	},

	onMediaEnded: function() {
		// Log a complete event.
		this.log({ action: 'complete', time: this.get( 'duration' ) });

		if ( this.get( 'repeat' ) ) {
			this.play();
		} else if (
			this.get( 'currentTrackIndex' ) < this.tracks.length - 1 ||
			this.get( 'loop' ) ||
			this.get( 'shuffle' )
		) {
			this.nextTrack();
			this.play();
		} else {
			this.nextTrack();
			this.stop();
		}
	},

	onMediaError: function( e ) {
		this.set( 'status', 'error' );
		if ( this.currentTrack ) {
			this.currentTrack.set( 'status', 'error' );
		}
		//clearInterval( this.playIntervalId );
		//this.nextTrack();
	},

	onMediaLoadedMetadata: function() {
		this.set( 'duration', this.media.duration );
	},

	onMediaPause: function() {
		this.set( 'status', 'paused' );
		this.currentTrack.set( 'status', 'paused' );
	},

	onMediaPlaying: function() {
		this.set( 'status', 'playing' );
		this.currentTrack.set( 'status', 'playing' );
	},

	onMediaVolumeChange: function() {
		this.set( 'volume', this.media.volume );
	},

	fetch: function() {
		var attributes;

		if ( ! this.options.persist || ! app.hasSessionStorage() ) {
			this.setCurrentTrack( this.get( 'currentTrackIndex' ) );
			return;
		}

		attributes = JSON.parse( sessionStorage.getItem( this.options.id ) );

		if ( null === attributes ) {
			this.setCurrentTrack( this.get( 'currentTrackIndex' ) );
			return;
		}

		this.setCurrentTrack ( attributes.currentTrackIndex );
		this.setCurrentTime( attributes.currentTime );
		this.initialCurrentTime = attributes.currentTime;

		this.set( 'loop', attributes.loop );
		this.set( 'repeat', attributes.repeat );
		this.set( 'shuffle', attributes.shuffle );
		this.setVolume( attributes.volume );

		// Don't auto play on mobile devices.
		if (
			'playing' === attributes.status &&
			! mejs.MediaFeatures.isAndroid &&
			! mejs.MediaFeatures.isiOS
		) {
			this.autoResumed = true;
			this.play();
		}
	},

	fetchTracks: function() {
		var cachedSignature;

		if ( ! app.hasSessionStorage() ) {
			return;
		}

		cachedSignature = sessionStorage.getItem( this.options.id + '-signature' );

		// Don't fetch tracks from sessionStorage if the signature has changed.
		if ( 'signature' in this.options && cachedSignature !== this.options.signature ) {
			sessionStorage.removeItem( this.options.id );
			sessionStorage.removeItem( this.options.id + '-signature' );
			sessionStorage.removeItem( this.options.id + '-tracks' );
			return;
		}

		this.tracks.fetch();
	},

	log: function( data ) {
		_.extend( data, {
			source: this.currentTrack.get( 'src' ),
			time: Math.round( this.get( 'currentTime' ) ),
			title: this.currentTrack.get( 'title' )
		});

		if ( global.cue && 'log' in global.cue ) {
			global.cue.log( data );
		}
	},

	save: function() {
		if ( ! this.options.persist || ! app.hasSessionStorage() ) {
			return;
		}

		sessionStorage.setItem( this.options.id, JSON.stringify( this.toJSON() ) );

		if ( 'signature' in this.options ) {
			sessionStorage.setItem( this.options.id + '-signature', this.options.signature );
		}
	}
});

module.exports = Player;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"../collections/tracks":2,"app":"app"}],5:[function(require,module,exports){
(function (global){
/*jshint browserify:true */
/*global JSON:false */

'use strict';

var Panel,
	app = require( 'app' )( 'marquee' ),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

Panel = Backbone.Model.extend({
	defaults: {
		id: 'default',
		isRestoring: false,
		status: 'closed'
	},

	initialize: function() {
		this.storageId = this.get( 'id' ) + '-panel';
		this.on( 'change:status', this.save );
	},

	close: function() {
		this.set( 'status', 'closed' );
	},

	fetch: function() {
		var panel;

		if ( ! app.hasSessionStorage() ) {
			return;
		}

		panel = JSON.parse( sessionStorage.getItem( this.storageId ) );

		if ( null !== panel && 'open' === panel.status ) {
			this.set( 'isRestoring', true );
			this.set( 'status', panel.status );
		}
	},

	isOpen: function() {
		return 'open' === this.get( 'status' );
	},

	open: function() {
		this.set( 'status', 'open' );
	},

	save: function() {
		if ( app.hasSessionStorage() ) {
			sessionStorage.setItem( this.storageId, JSON.stringify( this.toJSON() ) );
		}
	},

	toggle: function() {
		if ( this.isOpen() ) {
			this.close();
		} else {
			this.open();
		}
	}
});

module.exports = Panel;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"app":"app"}],6:[function(require,module,exports){
(function (global){
/*jshint browserify:true */
/*global mejs:false */

'use strict';

var Track,
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

Track = Backbone.Model.extend({
	defaults: {
		title: '',
		artist: '',
		album: '',
		artwork: '',
		duration: 0, // seconds
		length: '', // formatted
		src: '',
		status: '',
		type: '',
		order: 0,
		recordId: 0,
		trackId: 0
	},

	initialize: function( attributes, options ) {
		if ( 'string' === typeof attributes ) {
			this.set( 'src', attributes );
		}

		// Determine the type.
		if ( 'undefined' === typeof attributes.type || '' === attributes.type ) {
			if ( -1 !== this.get( 'src' ).indexOf( 'youtube' ) ) {
				this.set( 'type', 'audio/youtube' );
			} else {
				this.set( 'type', mejs.HtmlMediaElementShim.getTypeFromFile( this.get( 'src' ) ) );
			}
		}
	}
});

module.exports = Track;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],7:[function(require,module,exports){
(function (global){
/*jshint browserify:true, -W079 */

'use strict';

var $ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null);

$.event.special.swipe = {
	setup: function() {
		var $this = $( this ),
			originalPosition = null;

		function swipeInfo( event ) {
			var data = event.originalEvent.targetTouches ? event.originalEvent.targetTouches[0] : event.originalEvent,
				x = data.pageX,
				y = data.pageY,
				dx, dy;

			dx = ( x > originalPosition.x ) ? 'right' : 'left';
			dy = ( y > originalPosition.y ) ? 'down' : 'up';

			return {
				direction: {
					x: dx,
					y: dy
				},
				offset: {
					x: x - originalPosition.x,
					y: originalPosition.y - y
				}
			};
		}

		$this.on( 'touchstart mousedown', function ( event ) {
			var data = event.originalEvent.targetTouches ? event.originalEvent.targetTouches[0] : event.originalEvent;

			originalPosition = {
				x: data.pageX,
				y: data.pageY
			};
		});

		$this.on( 'touchend mouseup mouseout', function ( event ) {
			originalPosition = null;
		});

		$this.on( 'touchmove mousemove', function ( event ) {
			var data;

			if ( ! originalPosition ) {
				return;
			}

			data = swipeInfo( event );
			$this.trigger( 'swipe', data );

			if ( Math.abs( data.offset.x ) > 100 ) {
				$this.trigger( 'swipe' + data.direction.x, data );
			}
		});
	}
};

// Map swipe direction events to the main swipe event.
$.each({
	swipeleft: 'swipe',
	swiperight: 'swipe'
}, function( e, method ) {
	$.event.special[ e ] = {
		setup: function () {
			$( this ).on( method, $.noop );
		}
	};
});

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],8:[function(require,module,exports){
(function (global){
/*jshint browserify:true, -W079 */

'use strict';

var $ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	$window = $( window );

module.exports = {
	getViewportWidth: function() {
		return window.innerWidth || $window.width();
	},

	hasSessionStorage: function() {
		var mod = 'marquee';
		try {
			sessionStorage.setItem( mod, mod );
			sessionStorage.removeItem( mod );
			return true;
		} catch( e ) {
			return false;
		}
	},

	/**
	 * template( id )
	 *
	 * Fetch a JavaScript template for an id, and return a templating function for it.
	 *
	 * @param  {string} id   A string that corresponds to a DOM element with an id prefixed with "tmpl-".
	 *                       For example, "attachment" maps to "tmpl-attachment".
	 * @return {function}    A function that lazily-compiles the template requested.
	 */
	template: _.memoize(function( id ) {
		var compiled,
			options = {
				evaluate:    /<#([\s\S]+?)#>/g,
				interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
				escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
				variable:    'data'
			};

		return function( data ) {
			compiled = compiled || _.template( $( '#tmpl-' + id ).html(), null, options );
			return compiled( data );
		};
	})
};

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],9:[function(require,module,exports){
/*jshint browserify:true */

'use strict';

function WPEnqueues() {}

WPEnqueues.prototype.isDone = function( handle ) {
	return -1 !== this.done.indexOf( handle );
};

WPEnqueues.prototype.markAsDone = function( handles ) {
	if ( Array.isArray( handles ) ) {
		this.done = this.done.concat( handles );
	} else if ( 'string' === typeof handles ) {
		this.done.push( handles );
	}

	// Make the array contain unique values.
	this.done = this.done.filter(function( item, i ) {
		return this.done.indexOf( item ) === i;
	}, this ).sort();

	return this;
};

WPEnqueues.prototype.loadNewItems = function( items, callback ) {
	var i,
		handles = [];

	items = items || [];

	for ( i = 0; i < items.length; i++ ) {
		if ( ! this.isDone( items[ i ].handle ) ) {
			this.load( items[ i ], callback ).markAsDone( items[ i ].handle );
			handles.push( items[ i ].handle );
		}
	}

	return handles;
};

module.exports = WPEnqueues;

},{}],10:[function(require,module,exports){
/*jshint browserify:true */

'use strict';

var WPEnqueues = require ( './wp-enqueues' ),
	inherits = require( 'inherits' );

function WPScripts() {
	WPEnqueues.call( this );
	this.done = [];
}

inherits( WPScripts, WPEnqueues );

function loadScript( src, where, callback ) {
	var scriptTag = document.createElement( 'script' ),
		hasLoaded = false;

	if ( 'function' === typeof where ) {
		callback = where;
	}

	if ( 'string' !== typeof where ) {
		where = 'head';
	}

	scriptTag.setAttribute( 'src', src );

	// If there is a callback, run it when the script is loaded.
	if ( callback ) {
		scriptTag.onreadystatechange = scriptTag.onload = function () {
			if ( ! hasLoaded ) {
				callback();
			}
			hasLoaded = true;
		};
	}

	document.getElementsByTagName( where )[0].appendChild( scriptTag );
}

WPScripts.prototype.load = function( script, callback ) {
	var dataContent, dataTag,
		where = script.footer ? 'body' : 'head';

	// Output extra data, if present.
	if ( script.extra_data ) {
		dataTag = document.createElement( 'script' );
		dataContent = document.createTextNode( '//<![CDATA[ \n' + script.extra_data + '\n//]]>' );

		dataTag.type = 'text/javascript';
		dataTag.appendChild( dataContent );

		document.getElementsByTagName( where )[0].appendChild( dataTag );
	}

	loadScript( script.src, where, callback );

	return this;
};

module.exports = WPScripts;

},{"./wp-enqueues":9,"inherits":1}],11:[function(require,module,exports){
/*jshint browserify:true, evil:true */

'use strict';

var IEVersion,
	WPEnqueues = require ( './wp-enqueues' ),
	inherits = require( 'inherits' ),
	isIE = ( -1 !== navigator.userAgent.search( 'MSIE' ) );

// IE requires special handling.
if ( isIE ) {
	IEVersion = navigator.userAgent.match( /MSIE\s?(\d+)\.?\d*;/ );
	IEVersion = parseInt( IEVersion[1], 10 );
}

function WPStyles() {
	WPEnqueues.call( this );
	this.done = [];
}

inherits( WPStyles, WPEnqueues );

WPStyles.prototype.load = function( style ) {
	var tag = document.createElement( 'link' );

	tag.rel = 'stylesheet';
	tag.href = style.src;
	tag.id = style.handle + '-css';

	// Destroy link tag if a conditional statement is present and either the
	// browser isn't IE, or the conditional doesn't evaluate true.
	if ( style.conditional && ( ! isIE || ! eval( style.conditional.replace( /%ver/g, IEVersion ) ) ) ) {
		tag = false;
	}

	// Append link tag if necessary
	if ( tag ) {
		document.getElementsByTagName( 'head' )[0].appendChild( tag );
	}

	return this;
};

module.exports = WPStyles;

},{"./wp-enqueues":9,"inherits":1}],12:[function(require,module,exports){
(function (global){
/*jshint browserify:true, -W079 */
/*global _marqueeSettings:false, JSON:false */

'use strict';

var $ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	app = require( 'app' )( 'marquee' ),
	$content = $( '#content' ),
	utils = require( './modules/utils' ),
	WPScripts = require( './modules/wp-scripts' ),
	WPStyles = require( './modules/wp-styles' );

// Require jQuery plugins.
require( './modules/swipe-events' );
require( './vendor/appendaround.js' );

app.settings( _marqueeSettings || {});

// Mix in module methods.
_.extend( app, utils );

app.scripts = new WPScripts();
app.styles  = new WPStyles();

app.controllers.Page        = require( './controllers/page' );
app.controllers.Player      = require( './controllers/player' );
app.models.Panel            = require( './models/panel' );
app.models.Track            = require( './models/track' );
app.collections.Tracks      = require( './collections/tracks' );
app.views.Content           = require( './views/content' );
app.views.HomeWidgets       = require( './views/home-widgets' );
app.views.Page              = require( './views/Page' );
app.views.Panel             = require( './views/panel' );
app.views.PanelToggleButton = require( './views/panel-toggle-button' );
app.views.Record            = require( './views/record' );

app.page = new app.controllers.Page();

app.navigationPanel = new app.models.Panel({
	id: 'site-navigation'
});

app.playerPanel = new app.models.Panel({
	id: 'site-player'
});

new app.views.Page({
	el: document.getElementsByTagName( 'body' )[0],
	page: app.page,
	navigationPanel: app.navigationPanel,
	playerPanel: app.playerPanel,
	settings: app.settings()
});

new app.views.Panel({
	el: document.getElementById( 'site-navigation-panel' ),
	controller: app.navigationPanel,
	closeOn: 'swipeleft'
});

new app.views.PanelToggleButton({
	el: document.getElementById( 'site-navigation-toggle' ),
	controller: app.navigationPanel
});

new app.views.Content({
	el: document.getElementById( 'content' ),
	controller: app.page
});

app.pausePlayers = function() {
	_.each( app.players, function( player ) {
		if ( 'playing' === player.get( 'status' ) ) {
			player.pause();
		}
	});
};

$( document ).on( 'mq4hsChange', function ( e ) {
    $( document.documentElement ).toggleClass( 'hover', e.trueHover );
});

$( '.social-navigation' ).appendAround({
	set: $( '.site-header .panel-body-footer, .site-footer .site-info' )
});

$( '.credits' ).appendAround({
	set: $( '.site-header .panel-body-footer, .site-footer .site-info' )
});

// Cache the initial page state.
app.page.cacheState({
	bodyClasses: $( 'body' ).attr( 'class' )
});

// Set up the page when the state changes.
app.page.on( 'change:state', function() {
	var state = app.page.get( 'state' ),
		loadedScripts = [],
		loadedStyles = [];

	if ( state.scripts && state.styles ) {
		loadedStyles = app.styles.loadNewItems( state.styles );

		loadedScripts = app.scripts.loadNewItems( state.scripts, function() {
			//$body.trigger( 'post-load' );
			app.page.trigger( 'scripts-loaded' );
		});
	}

	app.page.set( 'loadedScripts', loadedScripts );
	app.page.set( 'loadedStyles', loadedStyles );
	app.page.updateUrl();
});

// Wire up PJAX and events.
if ( $.isFunction( $.pjax ) ) {
	$( '#page' )
		.pjax( 'a:not([href*="wp-admin"])', '#content', {
			fragment: 'body',
			timeout: 3500
		})
		.on( 'pjax:click', function( e ) {
			// @todo Consider checking response mime type instead.
			// @link https://github.com/defunkt/jquery-pjax/issues/533
			return ! /\.(jpe?g|png|gif|pdf|mp3)$/i.test( e.target.pathname );
		})
		.on( 'pjax:send', function() {
			app.page.set( 'isLoading', true );
		})
		.on( 'pjax:complete', function() {
			app.page.set( 'isLoading', false );
		}).on( 'pjax:end', function() {
			var $data = $content.find( '#pjax-data' ),
				data = $data.length ? JSON.parse( $data.html() ) : {};

			app.page.set( 'state', data );
		});
}

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./collections/tracks":2,"./controllers/page":3,"./controllers/player":4,"./models/panel":5,"./models/track":6,"./modules/swipe-events":7,"./modules/utils":8,"./modules/wp-scripts":10,"./modules/wp-styles":11,"./vendor/appendaround.js":13,"./views/Page":14,"./views/content":15,"./views/home-widgets":16,"./views/panel":18,"./views/panel-toggle-button":17,"./views/record":19,"app":"app"}],13:[function(require,module,exports){
/*!
 * appendAround markup pattern.
 * [c]2012, @scottjehl, Filament Group, Inc. MIT/GPL
 */
(function( $ ){
	$.fn.appendAround = function( options ) {
		return this.each(function() {
			var $self = $( this ),
				$parent = $self.parent(),
				parent = $parent[0],
				$set = options.set || $( '[data-set="' + $parent.attr( 'data-set' ) + '"]' );

			function isHidden( el ){
				return 'none' === $( el ).css( 'display' );
			}

			function appendToVisibleContainer(){
				if ( isHidden( parent ) ) {
					var found = 0;
					$set.each(function() {
						if ( ! isHidden( this ) && ! found ) {
							$self.appendTo( this );
							found++;
							parent = this;
						}
					});
				}
			}

			appendToVisibleContainer();

			$( window ).on( 'resize', appendToVisibleContainer );
		});
	};
})( jQuery );

},{}],14:[function(require,module,exports){
(function (global){
/*jshint browserify:true, -W079 */
/*global _wpmejsSettings:false, mejs:false */

'use strict';

var Page,
	$ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	app = require( 'app' )( 'marquee' ),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null),
	HomeWidgets = require( './home-widgets' );

Page = Backbone.View.extend({
	initialize: function( options ) {
		this.page = options.page;
		this.settings = options.settings;
		this.navigationPanel = options.navigationPanel;
		this.playerPanel = options.playerPanel;
		this.$html = $( 'html' );

		this.listenTo( this.page, 'change:mode',  this.maybeClosePanels );
		this.listenTo( this.page, 'change:mode',  this.maybeRestorePanels );
		this.listenTo( this.page, 'change:state', this.updateBodyClasses );
		this.listenTo( this.page, 'change:state', this.updateToolbarCustomizeLink );
		this.listenTo( this.page, 'change:state', this.updateToolbarEditLink );
		this.listenTo( this.page, 'change:url',   this.closePlayerPanel );
		this.listenTo( this.page, 'change:url',   this.initDropdowns );
		this.listenTo( this.page, 'change:url',   this.initExternalLinks );
		this.listenTo( this.page, 'change:url',   this.initHomeWidgetArea );
		this.listenTo( this.page, 'change:url',   this.initPopupLinks );
		this.listenTo( this.page, 'change:url',   this.initResponsiveVideos );
		this.listenTo( this.page, 'change:url',   this.initWPMediaElements );
		this.listenTo( this.page, 'change:state change:url', this.maybeCloseNavigationPanel );
	},

	/**
	 * Close the player panel.
	 */
	closePlayerPanel: function() {
		this.playerPanel.close();
	},

	/**
	 * Set up dropdown lists.
	 */
	initDropdowns: function() {
		$( '.dropdown-group' ).on( 'click', '.current-menu-item a, .dropdown-group-title', function( e ) {
			e.preventDefault();
			$( this ).closest( '.dropdown-group' ).toggleClass( 'is-open' );
		});
	},

	/**
	 * Open external links in a new window.
	 */
	initExternalLinks: function() {
		$( '.js-maybe-external' ).each(function() {
			if ( this.hostname && this.hostname !== window.location.hostname ) {
				$( this ).attr( 'target', '_blank' );
			}
		});
	},

	/**
	 * Set up and manage the homepage widget area.
	 */
	initHomeWidgetArea: function( page ) {
		var $widgetArea = $( '.home-widgets' );

		if ( $widgetArea.length < 1 ) {
			// Remove the existing view.
			if ( this.homeWidgetArea ) {
				this.homeWidgetArea.remove();
				this.homeWidgetArea = null;
			}

			return;
		}

		this.homeWidgetArea = new HomeWidgets({
			el: $widgetArea[0],
			page: page,
			isRTL: this.settings.isRTL
		}).render();
	},

	/**
	 * Open new windows for links with a class of '.js-popup'.
	 */
	initPopupLinks: function() {
		$( '.js-popup' ).on( 'click', function( e ) {
			var $this       = $( this ),
				popupId     = $this.data( 'popup-id' ) || 'popup',
				popupUrl    = $this.data( 'popup-url' ) || $this.attr( 'href' ),
				popupWidth  = $this.data( 'popup-width' ) || 550,
				popupHeight = $this.data( 'popup-height' ) || 260;

			e.preventDefault();

			window.open( popupUrl, popupId, [
				'width=' + popupWidth,
				'height=' + popupHeight,
				'directories=no',
				'location=no',
				'menubar=no',
				'scrollbars=no',
				'status=no',
				'toolbar=no'
			].join( ',' ) );
		});
	},

	/**
	 * Make videos responsive.
	 */
	initResponsiveVideos: function() {
		if ( $.isFunction( $.fn.fitVids ) ) {
			$( '.hentry, .widget' ).fitVids();
		}
	},

	/**
	 * Initialize media elements rendered by native WordPress shortcodes.
	 *
	 * Before initializing elements, existing instances are destroyed and the
	 * MediaElement.js containers are replaced with the actual media element HTML
	 * (<audio>, <video>) if necessary. This serves a couple of purposes:
	 *
	 * - Helps prevent ghost players from stacking up in the mejs.players global.
	 * - Keeps elements from being initalized more than once and becoming nested.
	 */
	initWPMediaElements: function( page ) {
		var mejsSelectors = '.wp-audio-shortcode, .wp-video-shortcode',
			mejsSettings = {},
			originalSuccessCb = _.noop;

		if ( 'undefined' !== typeof _wpmejsSettings ) {
			mejsSettings = _wpmejsSettings;
			originalSuccessCb = _wpmejsSettings.success;
		}

		if ( ! $.isFunction( $.fn.mediaelementplayer ) ) {
			return;
		}

		$( mejsSelectors ).filter( '.mejs-container' ).each(function( index, item ) {
			var $container = $( item ),
				$media = $container.find( 'audio, video' ),
				playerId = $( item ).attr( 'id' );

			try {
				mejs.players[ playerId ].remove();
				$container.replaceWith( $media );
			} catch ( e ) {}
		});

		// Pause custom players when a native element starts playing.
		mejsSettings.success = function( media, domObject, player ) {
			player.$media.on( 'play', app.pausePlayers );

			if ( originalSuccessCb ) {
				originalSuccessCb.call( this, media, domObject, player );
			}
		};

		$( mejsSelectors ).not( '.mejs-container' ).mediaelementplayer( mejsSettings );
	},

	/**
	 * Close the navigation panel on mobile and tablets when switching pages.
	 */
	maybeCloseNavigationPanel: function( page ) {
		if ( 'initial' !== page.previous( 'mode' ) && 'desktop' !== page.get( 'mode' ) ) {
			this.navigationPanel.close();
		}
	},

	/**
	 * Close panels when transitioning away from desktop.
	 */
	maybeClosePanels: function( page ) {
		if ( 'desktop' === page.previous( 'mode' ) ) {
			this.navigationPanel.close();
			this.playerPanel.close();
		}
	},

	/**
	 * Restore panels when refreshing a page.
	 */
	maybeRestorePanels: function( page ) {
		if ( 'initial' === page.previous( 'mode' ) && 'desktop' === page.get( 'mode' ) ) {
			//this.navigationPanel.fetch();
			//this.playerPanel.fetch();
		}
	},

	/**
	 * Update body classes when the page state changes.
	 */
	updateBodyClasses: function( page ) {
		var state = page.get( 'state' ),
			cache = page.getCachedState(),
			hasCustomizeSupport = this.$el.hasClass( 'customize-support' );

		if ( state.bodyClasses ) {
			this.$el.attr( 'class', state.bodyClasses );
		} else if ( cache.bodyClasses ) {
			this.$el.attr( 'class', cache.bodyClasses );
		}

		this.$el
			.toggleClass( 'customize-support', hasCustomizeSupport )
			.toggleClass( 'no-customize-support', ! hasCustomizeSupport );
	},

	/**
	 * Replace the Customize URL in the WP toolbar menu item.
	 */
	updateToolbarCustomizeLink: function( page ) {
		var state = page.get( 'state' );

		if ( state.customizeUrl ) {
			$( '#wp-admin-bar-customize a' ).attr( 'href', state.customizeUrl );
		}
	},

	/**
	 * Replace the edit link in the WP toolbar.
	 */
	updateToolbarEditLink: function( page ) {
		var state = page.get( 'state' );

		if ( state.editLink ) {
			$( '#wp-admin-bar-edit' ).html( state.editLink );
		}
	}
});

module.exports = Page;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{"./home-widgets":16,"app":"app"}],15:[function(require,module,exports){
(function (global){
/*jshint browserify:true */

'use strict';

var Content,
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

Content = Backbone.View.extend({
	initialize: function( options ) {
		this.controller = options.controller;
		this.listenTo( this.controller, 'change:isLoading', this.updateStatus );
	},

	updateStatus: function() {
		this.$el.toggleClass( 'is-loading', this.controller.get( 'isLoading' ) );
	}
});

module.exports = Content;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],16:[function(require,module,exports){
(function (global){
/*jshint browserify:true, -W079 */

'use strict';

var HomeWidgets,
	$ = (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

HomeWidgets = Backbone.View.extend({
	initialize: function( options ) {
		this.page = options.page;
		this.isRTL = options.isRTL;

		this.listenTo( this.page, 'change:mode', this.updateLayout );
		this.listenTo( this.page, 'scripts-loaded', this.updateLayout );
	},

	render: function() {
		this.updateLayout();
		return this;
	},

	setupMasonry: function() {
		this.$el.masonry({
			itemSelector : '.widget',
			isAnimated: false,
			isOriginLeft: this.isRTL,
			percentPosition: true
		});
	},

	updateLayout: function() {
		if ( $.fn.masonry && 'tablet' === this.page.get( 'mode' ) ) {
			this.setupMasonry();
		} else if ( this.$el.data( 'masonry' ) ) {
			this.$el.masonry( 'destroy' );
		}
	}
});

module.exports = HomeWidgets;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],17:[function(require,module,exports){
(function (global){
/*jshint browserify:true */

'use strict';

var PanelToggleButton,
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

PanelToggleButton = Backbone.View.extend({
	events: {
		'click': 'toggleStatus'
	},

	initialize: function( options ) {
		this.controller = options.controller;
		this.buttonText = options.buttonText;
		this.listenTo( this.controller, 'change:status', this.updateStatus );
	},

	render: function() {
		if ( this.buttonText.normal ) {
			this.$el.text( this.buttonText.normal );
		}
		return this;
	},

	toggleStatus: function( e ) {
		e.preventDefault();
		this.controller.toggle();
	},

	updateStatus: function() {
		if ( this.controller.isOpen() ) {
			this.$el.addClass( 'is-open' );
		} else {
			this.$el.removeClass( 'is-open' );
		}
	}
});

module.exports = PanelToggleButton;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],18:[function(require,module,exports){
(function (global){
/*jshint browserify:true */

'use strict';

var Panel,
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

Panel = Backbone.View.extend({
	events: {
		'swipeleft': 'onSwipe',
		'swiperight': 'onSwipe'
	},

	initialize: function( options ) {
		this.controller = options.controller;
		this.closeOn = options.closeOn || '';
		this.htmlClass = this.controller.get( 'id' ) + '-is-open';
		this.$html = Backbone.$( 'html' );

		this.listenTo( this.controller, 'change:status', this.updateStatus );
	},

	close: function() {
		this.$html.removeClass( this.htmlClass );
		this.$el.removeClass( 'is-animation-disabled is-open' );
	},

	onSwipe: function( e ) {
		if ( e.type === this.closeOn ) {
			this.controller.close();
		}
	},

	open: function() {
		if ( this.controller.get( 'isRestoring' ) ) {
			this.$el.addClass( 'is-animation-disabled' );
			this.controller.set( 'isRestoring', false );
		}

		this.$html.addClass( this.htmlClass );
		this.$el.addClass( 'is-open' );
	},

	updateStatus: function() {
		if ( this.controller.isOpen() ) {
			this.open();
		} else {
			this.close();
		}
	}
});

module.exports = Panel;

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}],19:[function(require,module,exports){
(function (global){
/*jshint browserify:true */
/*global mejs:false */

'use strict';

var Record,
	_ = (typeof window !== "undefined" ? window['_'] : typeof global !== "undefined" ? global['_'] : null),
	Backbone = (typeof window !== "undefined" ? window['Backbone'] : typeof global !== "undefined" ? global['Backbone'] : null);

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
},{}]},{},[12]);
