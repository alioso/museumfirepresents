/*jshint browserify:true */
/*global JSON:false */

'use strict';

var Tracks,
	_ = require( 'underscore' ),
	app = require( 'app' )( 'marquee' ),
	Backbone = require( 'backbone' ),
	Track = require( '../models/track' );

Tracks = Backbone.Collection.extend({
	model: Track,

	initialize: function( models, options ) {
		this.options = _.extend({
			id: 'site-player-tracks'
		}, options );
	},

	fetch: function() {
		var tracks;

		if ( app.hasSessionStorage() ) {
			tracks = JSON.parse( sessionStorage.getItem( this.options.id ) );

			if ( null !== tracks ) {
				this.reset( tracks );
			}
		}
	},

	save: function() {
		sessionStorage.setItem( this.options.id, JSON.stringify( this.toJSON() ) );
	}
});

module.exports = Tracks;
