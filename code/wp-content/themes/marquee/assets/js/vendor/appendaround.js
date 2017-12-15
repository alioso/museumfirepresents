/*!
 * appendAround markup pattern.
 * [c]2012, @scottjehl, Filament Group, Inc. MIT/GPL
 */
(function( $ ){
	$.fn.appendAround = function( options ) {
		return this.each(function() {
			var $self = $( this ),
				$parent = $self.parent(),
				parent = $parent[0],
				$set = options.set || $( '[data-set="' + $parent.attr( 'data-set' ) + '"]' );

			function isHidden( el ){
				return 'none' === $( el ).css( 'display' );
			}

			function appendToVisibleContainer(){
				if ( isHidden( parent ) ) {
					var found = 0;
					$set.each(function() {
						if ( ! isHidden( this ) && ! found ) {
							$self.appendTo( this );
							found++;
							parent = this;
						}
					});
				}
			}

			appendToVisibleContainer();

			$( window ).on( 'resize', appendToVisibleContainer );
		});
	};
})( jQuery );
