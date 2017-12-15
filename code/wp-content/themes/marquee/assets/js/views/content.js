/*jshint browserify:true */

'use strict';

var Content,
	Backbone = require( 'backbone' );

Content = Backbone.View.extend({
	initialize: function( options ) {
		this.controller = options.controller;
		this.listenTo( this.controller, 'change:isLoading', this.updateStatus );
	},

	updateStatus: function() {
		this.$el.toggleClass( 'is-loading', this.controller.get( 'isLoading' ) );
	}
});

module.exports = Content;
