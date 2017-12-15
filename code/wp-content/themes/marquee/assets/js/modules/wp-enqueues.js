/*jshint browserify:true */

'use strict';

function WPEnqueues() {}

WPEnqueues.prototype.isDone = function( handle ) {
	return -1 !== this.done.indexOf( handle );
};

WPEnqueues.prototype.markAsDone = function( handles ) {
	if ( Array.isArray( handles ) ) {
		this.done = this.done.concat( handles );
	} else if ( 'string' === typeof handles ) {
		this.done.push( handles );
	}

	// Make the array contain unique values.
	this.done = this.done.filter(function( item, i ) {
		return this.done.indexOf( item ) === i;
	}, this ).sort();

	return this;
};

WPEnqueues.prototype.loadNewItems = function( items, callback ) {
	var i,
		handles = [];

	items = items || [];

	for ( i = 0; i < items.length; i++ ) {
		if ( ! this.isDone( items[ i ].handle ) ) {
			this.load( items[ i ], callback ).markAsDone( items[ i ].handle );
			handles.push( items[ i ].handle );
		}
	}

	return handles;
};

module.exports = WPEnqueues;
