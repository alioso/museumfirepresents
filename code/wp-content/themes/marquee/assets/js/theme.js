/*jshint browserify:true, -W079 */
/*global _marqueeSettings:false, JSON:false */

'use strict';

var $ = require( 'jquery' ),
	_ = require( 'underscore' ),
	app = require( 'app' )( 'marquee' ),
	$content = $( '#content' ),
	utils = require( './modules/utils' ),
	WPScripts = require( './modules/wp-scripts' ),
	WPStyles = require( './modules/wp-styles' );

// Require jQuery plugins.
require( './modules/swipe-events' );
require( './vendor/appendaround.js');

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
