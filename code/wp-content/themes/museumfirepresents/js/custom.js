'use strict';

var $ = jQuery;

$('.audiotheme_gig .gig-venue-name').wrap('<a class="mapLink" href="javascript:" \>)');

$('.audiotheme_gig .venue-meta').hide().prepend('<a id="closeMap" href="javascript:">CLOSE</a>');

$('.mapLink').on('click', function() {
  $('.venue-meta').show();
});

$('#closeMap').on('click', function() {
  $('.venue-meta').hide();
});