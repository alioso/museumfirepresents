/*jshint browserify:true, -W079 */

'use strict';

var $ = require( 'jquery' ),
	_ = require( 'underscore' ),
	$window = $( window );

module.exports = {
	getViewportWidth: function() {
		return window.innerWidth || $window.width();
	},

	hasSessionStorage: function() {
		var mod = 'marquee';
		try {
			sessionStorage.setItem( mod, mod );
			sessionStorage.removeItem( mod );
			return true;
		} catch( e ) {
			return false;
		}
	},

	/**
	 * template( id )
	 *
	 * Fetch a JavaScript template for an id, and return a templating function for it.
	 *
	 * @param  {string} id   A string that corresponds to a DOM element with an id prefixed with "tmpl-".
	 *                       For example, "attachment" maps to "tmpl-attachment".
	 * @return {function}    A function that lazily-compiles the template requested.
	 */
	template: _.memoize(function( id ) {
		var compiled,
			options = {
				evaluate:    /<#([\s\S]+?)#>/g,
				interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
				escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
				variable:    'data'
			};

		return function( data ) {
			compiled = compiled || _.template( $( '#tmpl-' + id ).html(), null, options );
			return compiled( data );
		};
	})
};
