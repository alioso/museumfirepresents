(function( $, _, api, undefined ) {
	'use strict';

	api.controlConstructor['cedaro-theme-playlist'] = api.Control.extend({
		ready: function() {
			var frame,
				control = this;

			this.container.on( 'click', '.js-choose-playlist', function( e ) {
				var query,
					ids = _.compact( control.setting().split( ',' ) ),
					selection = [];

				e.preventDefault();

				if ( ids.length ) {
					query = wp.media.query({
						post__in : ids,
						orderby: 'post__in'
					});

					selection = new wp.media.model.Selection( query.models, {
						props: query.props.toJSON(),
						multiple: true
					});
				}

				// Dispose of any existing playlist frames.
				if ( frame ) {
					frame.dispose();
				}

				// Initialize the playlist frame.
				frame = wp.media({
					frame: 'post',
					state: ids.length ? 'playlist-edit' : 'playlist-library',
					library: {
						type: 'audio'
					},
					editing: true,
					multiple: true,
					selection: selection,
					sortable: true
				});

				frame.state( 'playlist-library' ).set( 'filterable', false );
				frame.state( 'playlist-edit' ).set( 'SettingsView', wp.media.view.Settings );

				// Set the target element's value to the playlist attachment IDs.
				frame.state( 'playlist-edit' ).on( 'update', function( selection ) {
					control.setting.set( selection.pluck( 'id' ).join( ',' ) );
				});

				frame.open();
			});
		}
	});

})( jQuery, _, wp.customize );
