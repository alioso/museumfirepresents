/*global _marqueeTracklistSettings:false */

window.marquee = window.marquee || {};

(function( $, undefined ) {
	'use strict';

	var player,
		app = window.marquee,
		$content = $( '#content' ),
		settings = app.settings( _marqueeTracklistSettings );

	// @todo Try to make feedback more reponsive (takes awhile for pause icon to appear.

	function initPlayer() {
		var tracks;

		if ( player || 'undefined' === typeof mejs ) {
			return;
		}

		// Ensure the first track always has a source so the audio can be
		// 'unlocked' during the first user gesture on iOS 9. Otherwise, the
		// audio is loaded during an XHR request, which isn't in the same
		// process as the user interaction that initiated the event.
		tracks = new app.collections.Tracks([{
			src: settings.templateUrl + '/assets/silent.mp3'
		}]);

		player = app.players.tracklists = new app.controllers.Player({
			loop: false
		}, {
			id: 'tracklists',
			mejsPluginPath: settings.mejs.pluginPath,
			players: app.players,
			tracks: tracks
		});

		player.fetch();
	}

	function initRecords() {
		$content.find( '.js-play-record' ).off( 'click' ).each(function() {
			var $this = $( this );

			new app.views.Record({
				el: this,
				controller: player,
				settings: settings,
				recordId: $this.data( 'record-id' ),
				trackId: $this.data( 'track-id' ),
				isSingle: !! $this.data( 'single' )
			}).render();
		});
	}

	function pausePlayer() {
		// Remove the 'is-playing' class so it doesn't get cached by PJAX.
		$content.find( '.js-play-record' ).removeClass( 'is-playing' );

		if ( player && 'playing' === player.get( 'status' ) ) {
			player.pause();
		}
	}

	function init() {
		initPlayer();
		initRecords();
	}

	init();
	app.page.on( 'change:url', init );
	$( document ).on( 'pjax:beforeSend', pausePlayer );
})( jQuery );
