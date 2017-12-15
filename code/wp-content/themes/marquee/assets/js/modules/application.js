/*jshint browserify:true */

'use strict';

var _ = require( 'underscore' );

function Application() {
	var settings = {};

	_.extend( this, {
		controllers: {},
		collections: {},
		l10n: {},
		models: {},
		players: {},
		routers: {},
		views: {}
	});

	this.settings = function( options ) {
		if ( options ) {
			_.extend( settings, options );
		}

		if ( settings.l10n ) {
			this.l10n = _.extend( this.l10n, settings.l10n );
			delete settings.l10n;
		}

		return settings || {};
	};
}

module.exports = function( name ) {
	global[ name ] = global[ name ] || new Application();
	return global[ name ];
};
