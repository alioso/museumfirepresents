/*jshint browserify:true */

'use strict';

var PanelToggleButton,
	Backbone = require( 'backbone' );

PanelToggleButton = Backbone.View.extend({
	events: {
		'click': 'toggleStatus'
	},

	initialize: function( options ) {
		this.controller = options.controller;
		this.buttonText = options.buttonText;
		this.listenTo( this.controller, 'change:status', this.updateStatus );
	},

	render: function() {
		if ( this.buttonText.normal ) {
			this.$el.text( this.buttonText.normal );
		}
		return this;
	},

	toggleStatus: function( e ) {
		e.preventDefault();
		this.controller.toggle();
	},

	updateStatus: function() {
		if ( this.controller.isOpen() ) {
			this.$el.addClass( 'is-open' );
		} else {
			this.$el.removeClass( 'is-open' );
		}
	}
});

module.exports = PanelToggleButton;
