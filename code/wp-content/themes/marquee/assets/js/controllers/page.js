/*jshint browserify:true */

'use strict';

var Page,
	_ = require( 'underscore' ),
	Backbone = require( 'backbone' ),
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
