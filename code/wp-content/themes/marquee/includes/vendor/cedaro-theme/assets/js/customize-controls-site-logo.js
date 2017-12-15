(function( $, _, api, undefined ) {
	'use strict';

	jQuery( document ).ready(function() {

		/**
		 * Site Logo control.
		 *
		 * Maps changes changes to the `cedaro_site_logo` setting to the
		 * `site_logo` setting, which is the option Jetpack uses for its Site
		 * Logo feature. The default image control saves attachment URLs, so
		 * this captures the other attachment data needed for compatibility with
		 * the exepected `site_logo` format.
		 */
		api.control( 'cedaro_site_logo', function( control ) {
			var attachments = [],
				siteLogo = api( 'site_logo' );

			// Add the initial site logo to the local attachments collection.
			if ( siteLogo.get().id ) {
				attachments.push( siteLogo.get() );

				// Set the control setting URL to match the site logo option.
				// These could get out of sync if a logo plugin is deactivated.
				control.setting.set( siteLogo.get().url );
			}

			// Update the site logo setting when the control setting value is changed.
			control.setting.bind(function( value ) {
				var attachment;

				if ( ! _.isUndefined( control.params.attachment ) ) {
					attachment = control.params.attachment;
				} else if ( value ) {
					// For WP < 4.1: Look up the site logo in the local collection of uploaded images.
					attachment = _.findWhere( attachments, { url: value });
				}

				if ( ! _.isNull( attachment ) && ! _.isUndefined( attachment ) ) {
					siteLogo.set( attachment );
				} else {
					siteLogo.set({ id: 0, url: '' });
				}
			});

			// For WP < 4.1: Captures attachments uploaded via the control.
			if ( ! _.isUndefined( control.uploader ) ) {
				// Proxy the ImageControl success callback.
				control.uploader.success = function( attachment ) {
					var props = _.pick( attachment.toJSON(), 'id', 'sizes', 'url' );

					api.ImageControl.prototype.success.call( control, attachment );

					attachments.push( props );
					siteLogo.set( props );
				};
			}
		});

	});

})( jQuery, _, wp.customize );
