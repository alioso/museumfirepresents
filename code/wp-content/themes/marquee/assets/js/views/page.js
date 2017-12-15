/*jshint browserify:true, -W079 */
/*global _wpmejsSettings:false, mejs:false */

'use strict';

var Page,
	$ = require( 'jquery' ),
	_ = require( 'underscore' ),
	app = require( 'app' )( 'marquee' ),
	Backbone = require( 'backbone' ),
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
