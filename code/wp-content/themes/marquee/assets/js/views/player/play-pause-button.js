/*jshint browserify:true */

'use strict';

var PlayPauseButton,
	Backbone = require( 'backbone' );

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
