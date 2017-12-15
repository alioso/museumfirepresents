/*jshint browserify:true */

'use strict';

var Panel,
	Backbone = require( 'backbone' );

Panel = Backbone.View.extend({
	events: {
		'swipeleft': 'onSwipe',
		'swiperight': 'onSwipe'
	},

	initialize: function( options ) {
		this.controller = options.controller;
		this.closeOn = options.closeOn || '';
		this.htmlClass = this.controller.get( 'id' ) + '-is-open';
		this.$html = Backbone.$( 'html' );

		this.listenTo( this.controller, 'change:status', this.updateStatus );
	},

	close: function() {
		this.$html.removeClass( this.htmlClass );
		this.$el.removeClass( 'is-animation-disabled is-open' );
	},

	onSwipe: function( e ) {
		if ( e.type === this.closeOn ) {
			this.controller.close();
		}
	},

	open: function() {
		if ( this.controller.get( 'isRestoring' ) ) {
			this.$el.addClass( 'is-animation-disabled' );
			this.controller.set( 'isRestoring', false );
		}

		this.$html.addClass( this.htmlClass );
		this.$el.addClass( 'is-open' );
	},

	updateStatus: function() {
		if ( this.controller.isOpen() ) {
			this.open();
		} else {
			this.close();
		}
	}
});

module.exports = Panel;
