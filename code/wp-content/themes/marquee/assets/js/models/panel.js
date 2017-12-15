/*jshint browserify:true */
/*global JSON:false */

'use strict';

var Panel,
	app = require( 'app' )( 'marquee' ),
	Backbone = require( 'backbone' );

Panel = Backbone.Model.extend({
	defaults: {
		id: 'default',
		isRestoring: false,
		status: 'closed'
	},

	initialize: function() {
		this.storageId = this.get( 'id' ) + '-panel';
		this.on( 'change:status', this.save );
	},

	close: function() {
		this.set( 'status', 'closed' );
	},

	fetch: function() {
		var panel;

		if ( ! app.hasSessionStorage() ) {
			return;
		}

		panel = JSON.parse( sessionStorage.getItem( this.storageId ) );

		if ( null !== panel && 'open' === panel.status ) {
			this.set( 'isRestoring', true );
			this.set( 'status', panel.status );
		}
	},

	isOpen: function() {
		return 'open' === this.get( 'status' );
	},

	open: function() {
		this.set( 'status', 'open' );
	},

	save: function() {
		if ( app.hasSessionStorage() ) {
			sessionStorage.setItem( this.storageId, JSON.stringify( this.toJSON() ) );
		}
	},

	toggle: function() {
		if ( this.isOpen() ) {
			this.close();
		} else {
			this.open();
		}
	}
});

module.exports = Panel;
