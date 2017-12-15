/*jshint browserify:true */
/*global mejs:false */

'use strict';

var Track,
	Backbone = require( 'backbone' );

Track = Backbone.Model.extend({
	defaults: {
		title: '',
		artist: '',
		album: '',
		artwork: '',
		duration: 0, // seconds
		length: '', // formatted
		src: '',
		status: '',
		type: '',
		order: 0,
		recordId: 0,
		trackId: 0
	},

	initialize: function( attributes, options ) {
		if ( 'string' === typeof attributes ) {
			this.set( 'src', attributes );
		}

		// Determine the type.
		if ( 'undefined' === typeof attributes.type || '' === attributes.type ) {
			if ( -1 !== this.get( 'src' ).indexOf( 'youtube' ) ) {
				this.set( 'type', 'audio/youtube' );
			} else {
				this.set( 'type', mejs.HtmlMediaElementShim.getTypeFromFile( this.get( 'src' ) ) );
			}
		}
	}
});

module.exports = Track;
