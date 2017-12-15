/*global _marqueePlayerSettings, JSON:false */
/*jshint browserify:true, -W079 */

'use strict';

var currentTrackDetails, playPauseButton, player, playerView, tracks,
	$ = require( 'jquery' ),
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
