(function( window, $, undefined ) {
	'use strict';

	var billboard = {};

	$.extend( billboard, {
		settings: {
			audioVolume: 'vertical',
			cuePlaylistLoop: false,
			cuePlaylistToggle: '',
			cueResponsiveProgress: true,
			cueSelectors: {
				playlist: '.billboard-player',
				tracklist: '.cue-tracks'
			},
			cueSkin: 'cue-skin-billboard',
			features: [
				'cuecurrentdetails',
				'cueprevioustrack',
				'playpause',
				'cuenexttrack',
				'progress',
				'current',
				'duration',
				'volume',
				'cueplaylist',
				'cueplaylisttoggle'
			]
		}
	});

	$( document ).ready(function( $ ) {
		var $window = $( window ),
			$toolbar = $( '#wpadminbar' ),
			$player = $( '.billboard-player' ),
			$tracklist = $player.find( '.cue-tracks' ),
			throttle = false;

		$window.on( 'load resize orientationchange scroll', function() {
			if ( throttle ) {
				return;
			}

			throttle = true;
			setTimeout(function() {
				var windowHeight = window.innerHeight || $window.height();
				$tracklist.css( 'maxHeight', windowHeight - $toolbar.height() - $player.height() + 'px' );
				throttle = false;
			}, 250 );
		});

		// Initialize the player.
		$player.cuePlaylist( billboard.settings );
	});

})( window, jQuery );
