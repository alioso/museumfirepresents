/*jshint browserify:true */

'use strict';

var VolumePanel,
	_ = require( 'underscore' ),
	Backbone = require( 'backbone' ),
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
