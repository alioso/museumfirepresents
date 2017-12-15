/*jshint browserify:true, -W079 */

'use strict';

var HomeWidgets,
	$ = require( 'jquery' ),
	Backbone = require( 'backbone' );

HomeWidgets = Backbone.View.extend({
	initialize: function( options ) {
		this.page = options.page;
		this.isRTL = options.isRTL;

		this.listenTo( this.page, 'change:mode', this.updateLayout );
		this.listenTo( this.page, 'scripts-loaded', this.updateLayout );
	},

	render: function() {
		this.updateLayout();
		return this;
	},

	setupMasonry: function() {
		this.$el.masonry({
			itemSelector : '.widget',
			isAnimated: false,
			isOriginLeft: this.isRTL,
			percentPosition: true
		});
	},

	updateLayout: function() {
		if ( $.fn.masonry && 'tablet' === this.page.get( 'mode' ) ) {
			this.setupMasonry();
		} else if ( this.$el.data( 'masonry' ) ) {
			this.$el.masonry( 'destroy' );
		}
	}
});

module.exports = HomeWidgets;
