/*jshint browserify:true, -W079 */

'use strict';

var $ = require( 'jquery' );

$.event.special.swipe = {
	setup: function() {
		var $this = $( this ),
			originalPosition = null;

		function swipeInfo( event ) {
			var data = event.originalEvent.targetTouches ? event.originalEvent.targetTouches[0] : event.originalEvent,
				x = data.pageX,
				y = data.pageY,
				dx, dy;

			dx = ( x > originalPosition.x ) ? 'right' : 'left';
			dy = ( y > originalPosition.y ) ? 'down' : 'up';

			return {
				direction: {
					x: dx,
					y: dy
				},
				offset: {
					x: x - originalPosition.x,
					y: originalPosition.y - y
				}
			};
		}

		$this.on( 'touchstart mousedown', function ( event ) {
			var data = event.originalEvent.targetTouches ? event.originalEvent.targetTouches[0] : event.originalEvent;

			originalPosition = {
				x: data.pageX,
				y: data.pageY
			};
		});

		$this.on( 'touchend mouseup mouseout', function ( event ) {
			originalPosition = null;
		});

		$this.on( 'touchmove mousemove', function ( event ) {
			var data;

			if ( ! originalPosition ) {
				return;
			}

			data = swipeInfo( event );
			$this.trigger( 'swipe', data );

			if ( Math.abs( data.offset.x ) > 100 ) {
				$this.trigger( 'swipe' + data.direction.x, data );
			}
		});
	}
};

// Map swipe direction events to the main swipe event.
$.each({
	swipeleft: 'swipe',
	swiperight: 'swipe'
}, function( e, method ) {
	$.event.special[ e ] = {
		setup: function () {
			$( this ).on( method, $.noop );
		}
	};
});
