/*jshint browserify:true */

'use strict';

var Playlist,
	Backbone = require( 'backbone' ),
	Track = require( './track' );

Playlist = Backbone.View.extend({
	className: 'playlist',
	tagName: 'div',

	initialize: function( options ) {
		this.controller = options.controller;

		this.listenTo( this.controller.tracks, 'add', this.addTrack );
	},

	render: function() {
		this.$el.empty().append( '<ol class="tracks-list"></ol>' );
		this.$tracksList = this.$el.find( '.tracks-list' );
		this.controller.tracks.each( this.addTrack, this );
		return this;
	},

	addTrack: function( track ) {
		this.$tracksList.append(
			new Track({
				model: track,
				controller: this.controller
			}).render().el
		);
	}
});

module.exports = Playlist;
