/*global _:false, _cueproInsights:false, MediaElementPlayer:false, mejs:false */

window.cue = window.cue || {};

(function( window, $, undefined ) {
	'use strict';

	var listened,
		cue = window.cue;

	/**
	 * Get a unique id for the client.
	 *
	 * Saves the id to a cookie for subsequent visits.
	 *
	 * @return {string}
	 */
	function getClientUid() {
		var expires,
			clientUid = getCookieValue( 'cue_cid' );

		if ( ! clientUid ) {
			// http://stackoverflow.com/a/25065857
			clientUid = Math.floor( Math.random() * 0x7FFFFFFF ) + '.' + Math.floor( Date.now() / 1000 );

			expires = new Date();
			expires.setDate( expires.getDate() + 365 );
			document.cookie = 'cue_cid=u' + clientUid + '; expires=' + expires.toUTCString() + '; path=/';
		}

		return clientUid;
	}

	/**
	 * Get a cookie value by name.
	 *
	 * @param  {string} name Cookie name.
	 * @return {string}
	 */
	function getCookieValue( name ) {
		var re = new RegExp( '(?:(?:^|.*;\\s*)' + name + '\\s*\\=\\s*([^;]*).*$)|^.*$' );
		return document.cookie.replace( re, '$1' );
	}

	/**
	 * Retrieve details about the currently playing track for a MediaElement.js
	 * player.
	 *
	 * @param {Object} player MediaElementPlayer object.
	 * @return {Object}
	 */
	function getCurrentTrack( player ) {
		var track = {};

		if ( 'cueGetCurrentTrack' in player ) {
			$.extend( track, player.cueGetCurrentTrack() );
		}

		// Get the title from the <audio> title attribute.
		if ( ! track.title ) {
			track.title = player.$media.attr( 'title' );
		}

		return $.extend({}, {
			currentTime: player.media.currentTime,
			duration: player.media.duration,
			src: player.media.src,
			title: track.title || ''
		});
	}

	$.extend( cue, {
		log: function( data ) {
			return $.ajax({
				url: _cueproInsights.routeUrl,
				type: 'POST',
				data: {
					action: data.action,
					client_uid: getClientUid(),
					page_title: $( document ).find( 'title' ).text(),
					page_url: window.location.href,
					target_time: data.time,
					target_title: data.title,
					target_url: data.source
				}
			});
		}
	});

	$.extend( MediaElementPlayer.prototype, {
		buildcueinsights: function( player, controls, layers, media ) {
			var counters = {},
				skipLock = false;

			if ( 'AUDIO' !== media.tagName ) {
				return;
			}

			/**
			 * Log a play event.
			 *
			 * Attempts to log only the initial play event. Skips play events
			 * triggered by the auto-resume functionality provided by the
			 * history feature.
			 */
			player.$media.on( 'play', _.debounce(function( e ) {
				var counter = counters.play || 0,
					track = getCurrentTrack( player ),
					time = Math.round( track.currentTime );

				// Remove 'time < 2' to log more play events.
				if ( ( ! player.cueAutoResume || counter ) && time < 2 ) {
					cue.log({
						action: 'play',
						source: track.src,
						time: time < 2 ? 0 : time,
						title: track.title
					});

					skipLock = false;
				}

				counters.play = counter + 1;
			}, 250 ) );

			/**
			 * Log a listen event.
			 */
			player.$media.on( 'timeupdate', function( e ) {
				var track,
					threshold = media.duration * 0.2;

				if ( listened < threshold && media.currentTime > threshold ) {
					track = getCurrentTrack( player );
					cue.log({
						action: 'listen',
						source: track.src,
						time: Math.round( track.currentTime ),
						title: track.title
					});
				}

				listened = media.currentTime;
			});

			/**
			 * Log a skip event.
			 *
			 * Attempt to prevent successive skip events with a lock so stats
			 * won't be skewed in weird ways.
			 */
			player.$node.on( 'skipNext.cue', function( e, state, track ) {
				if ( ! skipLock ) {
					cue.log({
						action: 'skip',
						source: state.src,
						time: Math.round( state.currentTime ),
						title: track.title || ''
					});
				}

				skipLock = true;
			});

			/**
			 * Log an end event.
			 */
			player.$media.on( 'ended', function( e ) {
				var track = getCurrentTrack( player );
				cue.log({
					action: 'complete',
					source: track.src,
					time: Math.round( track.duration ),
					title: track.title
				});
			});

			player.$media.on( 'seeked', function( e ) {
				var counter = counters.seek || 0,
					track = getCurrentTrack( player ),
					time = Math.round( track.currentTime );

				// Skip the first event if it's triggered by the history feature.
				if ( time > 1 && ( ! player.cueAutoResume || counter ) ) {
					//cue.log( track.src, 'seek', time, track.title );
				}

				counters.seek = counter + 1;
			});

			player.$media.on( 'pause', _.debounce(function( e ) {
				var track = getCurrentTrack( player ),
					currentTime = Math.round( track.currentTime ),
					duration = Math.round( track.duration );

				// Don't log pause events triggered when a track ends.
				if ( currentTime > 2 && currentTime !== duration ) {
					//cue.log( track.src, 'pause', currentTime, track.title );
				}
			}, 250 ));
		}
	});

	/**
	 * Proxy the MediaElementPlayer init method to add the 'cueinsights'
	 * feature.
	 *
	 * This should work for any player registered using MediaElementPlayer(),
	 * including Cue and core shortcodes.
	 */
	var mePlayerInit = mejs.MediaElementPlayer.prototype.init;
	mejs.MediaElementPlayer.prototype.init = function() {
		if ( -1 === $.inArray( 'cueinsights', this.options.features ) ) {
			this.options.features.unshift( 'cueinsights' );
		}

		mePlayerInit.call( this );
	};

	/**
	 * Native WordPress playlist support.
	 */
	if ( 'WPPlaylistView' in window ) {
		var WPPlaylistViewRenderCurrent = window.WPPlaylistView.prototype.renderCurrent;

		/**
		 * Set the title attribute on the <audio> node when the current track
		 * changes in a native playlist.
		 *
		 * This allows the track title to be logged with any events.
		 */
		window.WPPlaylistView.prototype.renderCurrent = function() {
			WPPlaylistViewRenderCurrent.apply( this, arguments );
			$( this.playerNode ).attr( 'title', this.current.get( 'title' ) );
		};
	}

})( window, jQuery );
