/*global _cueproSettings:false, MediaElementPlayer:false */

window.cue = window.cue || {};

(function( window, $, undefined ) {
	'use strict';

	var cue = window.cue,
		POPUP_ICON = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 16 16"><path d="M2,5h9v9H2V5z"/><polygon points="5,2 5,4 12,4 12,11 14,11 14,2 "/></svg>',
		SHARE_ICON = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 24 24"><path d="M17,15c-0.724,0-1.38,0.267-1.898,0.695L9.94,12.598C9.979,12.404,10,12.205,10,12s-0.021-0.404-0.06-0.598l5.162-3.097 C15.62,8.733,16.276,9,17,9c1.657,0,3-1.343,3-3s-1.343-3-3-3s-3,1.343-3,3c0,0.205,0.021,0.404,0.06,0.598L8.898,9.695 C8.38,9.267,7.724,9,7,9c-1.657,0-3,1.343-3,3s1.343,3,3,3c0.724,0,1.38-0.267,1.898-0.695l5.162,3.097 C14.021,17.596,14,17.795,14,18c0,1.657,1.343,3,3,3s3-1.343,3-3S18.657,15,17,15z"/></svg>';

	cue.l10n = $.extend({
		popup: 'Popup',
		share: 'Share'
	}, cue.l10n, _cueproSettings.l10n );

	$.fn.cuePlaylist.features.push( 'cuepopup' );
	$.fn.cuePlaylist.features.push( 'cueshare' );

	$.extend( MediaElementPlayer.prototype, {
		buildcuepopup: function( player, controls, layers, media ) {
			if (
				! ( 'cuePermalink' in player.options ) ||
				'' === player.options.cuePermalink ||
				'#popup' === window.location.hash
			) {
				return;
			}

			$( '<div class="mejs-button mejs-popup-button mejs-popup">' +
				'<button type="button" aria-controls="' + player.id + '" title="' + cue.l10n.popup + '">' + POPUP_ICON + '</button>' +
				'</div>' )
				.appendTo( controls )
				.on( 'click.cue', function( e ) {
					var playerWidth = player.container.width();

					e.preventDefault();

					window.open( player.options.cueEmbedLink + '#popup', 'cue', [
						'screenX=40',
						'screenY=40',
						'width=' + ( playerWidth < 550 ? playerWidth : 550 ),
						'height=' + 387,
						'directories=no',
						'location=no',
						'menubar=no',
						'scrollbars=no',
						'status=no',
						'toolbar=no'
					].join( ',' ) );
				});
		}
	});

	$.extend( MediaElementPlayer.prototype, {
		buildcueshare: function( player, controls, layers, media ) {
			var selectors = player.options.cueSelectors,
				$playlist = player.container.closest( selectors.playlist ),
				$panel = $playlist.find( '.cue-share-dialog' );

			if ( _cueproSettings.disableEmbeds ) {
				return;
			}

			$playlist.addClass( 'is-embeddable' );

			$( '<div class="mejs-button mejs-share-button mejs-share">' +
				'<button type="button" aria-controls="' + player.id + '" title="' + cue.l10n.share + '">' + SHARE_ICON + '</button>' +
				'</div>' )
				.appendTo( controls )
				.on( 'click.cue', function() {
					$panel.show();
				});

			$panel.on( 'click.cue', '.js-close', function() {
				$panel.hide();
			});
		}
	});

})( window, jQuery );
