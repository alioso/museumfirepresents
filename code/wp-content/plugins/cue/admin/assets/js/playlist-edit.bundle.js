/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 14);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

module.exports = wp;

/***/ }),
/* 1 */
/***/ (function(module, exports) {

module.exports = _;

/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
/* WEBPACK VAR INJECTION */(function(global) {

var _ = __webpack_require__(1);

function Application() {
	var settings = {};

	_.extend(this, {
		controller: {},
		l10n: {},
		model: {},
		view: {}
	});

	this.settings = function (options) {
		if (options) {
			_.extend(settings, options);
		}

		if (settings.l10n) {
			this.l10n = _.extend(this.l10n, settings.l10n);
			delete settings.l10n;
		}

		return settings || {};
	};
}

global.cue = global.cue || new Application();
module.exports = global.cue;
/* WEBPACK VAR INJECTION */}.call(exports, __webpack_require__(6)))

/***/ }),
/* 3 */
/***/ (function(module, exports) {

module.exports = jQuery;

/***/ }),
/* 4 */
/***/ (function(module, exports) {

module.exports = Backbone;

/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var Workflows,
    _ = __webpack_require__(1),
    cue = __webpack_require__(2),
    l10n = __webpack_require__(2).l10n,
    MediaFrame = __webpack_require__(8),
    wp = __webpack_require__(0),
    Attachment = wp.media.model.Attachment;

Workflows = {
	frames: [],
	model: {},

	/**
  * Set a model for the current workflow.
  *
  * @param {Object} frame
  */
	setModel: function setModel(model) {
		this.model = model;
		return this;
	},

	/**
  * Retrieve or create a frame instance for a particular workflow.
  *
  * @param {string} id Frame identifer.
  */
	get: function get(id) {
		var method = '_' + id,
		    frame = this.frames[method] || null;

		// Always call the frame method to perform any routine set up. The
		// frame method should short-circuit before being initialized again.
		frame = this[method].call(this, frame);

		// Store the frame for future use.
		this.frames[method] = frame;

		return frame;
	},

	/**
  * Workflow for adding tracks to the playlist.
  *
  * @param {Object} frame
  */
	_addTracks: function _addTracks(frame) {
		// Return the existing frame for this workflow.
		if (frame) {
			return frame;
		}

		// Initialize the audio frame.
		frame = new MediaFrame({
			title: l10n.workflows.addTracks.frameTitle,
			library: {
				type: 'audio'
			},
			button: {
				text: l10n.workflows.addTracks.frameButtonText
			},
			multiple: 'add'
		});

		// Set the extensions that can be uploaded.
		frame.uploader.options.uploader.plupload = {
			filters: {
				mime_types: [{
					title: l10n.workflows.addTracks.fileTypes,
					extensions: 'm4a,mp3,ogg,wma'
				}]
			}
		};

		// Prevent the Embed controller scanner from changing the state.
		frame.state('embed').props.off('change:url', frame.state('embed').debouncedScan);

		// Insert each selected attachment as a new track model.
		frame.state('insert').on('insert', function (selection) {
			_.each(selection.models, function (attachment) {
				cue.tracks.push(attachment.toJSON().cue);
			});
		});

		// Insert the embed data as a new model.
		frame.state('embed').on('select', function () {

			var embed = this.props.toJSON(),
			    track = {
				audioId: '',
				audioUrl: embed.url
			};

			if ('title' in embed && '' !== embed.title) {
				track.title = embed.title;
			}

			cue.tracks.push(track);
		});

		return frame;
	},

	/**
  * Workflow for selecting track artwork image.
  *
  * @param {Object} frame
  */
	_selectArtwork: function _selectArtwork(frame) {
		var workflow = this;

		// Return existing frame for this workflow.
		if (frame) {
			return frame;
		}

		// Initialize the artwork frame.
		frame = wp.media({
			title: l10n.workflows.selectArtwork.frameTitle,
			library: {
				type: 'image'
			},
			button: {
				text: l10n.workflows.selectArtwork.frameButtonText
			},
			multiple: false
		});

		// Set the extensions that can be uploaded.
		frame.uploader.options.uploader.plupload = {
			filters: {
				mime_types: [{
					files: l10n.workflows.selectArtwork.fileTypes,
					extensions: 'jpg,jpeg,gif,png'
				}]
			}
		};

		// Automatically select the existing artwork if possible.
		frame.on('open', function () {
			var selection = this.get('library').get('selection'),
			    artworkId = workflow.model.get('artworkId'),
			    attachments = [];

			if (artworkId) {
				attachments.push(Attachment.get(artworkId));
				attachments[0].fetch();
			}

			selection.reset(attachments);
		});

		// Set the model's artwork ID and url properties.
		frame.state('library').on('select', function () {
			var attachment = this.get('selection').first().toJSON();

			workflow.model.set({
				artworkId: attachment.id,
				artworkUrl: attachment.sizes.cue.url
			});
		});

		return frame;
	},

	/**
  * Workflow for selecting track audio.
  *
  * @param {Object} frame
  */
	_selectAudio: function _selectAudio(frame) {
		var workflow = this;

		// Return the existing frame for this workflow.
		if (frame) {
			return frame;
		}

		// Initialize the audio frame.
		frame = new MediaFrame({
			title: l10n.workflows.selectAudio.frameTitle,
			library: {
				type: 'audio'
			},
			button: {
				text: l10n.workflows.selectAudio.frameButtonText
			},
			multiple: false
		});

		// Set the extensions that can be uploaded.
		frame.uploader.options.uploader.plupload = {
			filters: {
				mime_types: [{
					title: l10n.workflows.selectAudio.fileTypes,
					extensions: 'm4a,mp3,ogg,wma'
				}]
			}
		};

		// Prevent the Embed controller scanner from changing the state.
		frame.state('embed').props.off('change:url', frame.state('embed').debouncedScan);

		// Set the frame state when opening it.
		frame.on('open', function () {
			var selection = this.get('insert').get('selection'),
			    audioId = workflow.model.get('audioId'),
			    audioUrl = workflow.model.get('audioUrl'),
			    isEmbed = audioUrl && !audioId,
			    attachments = [];

			// Automatically select the existing audio file if possible.
			if (audioId) {
				attachments.push(Attachment.get(audioId));
				attachments[0].fetch();
			}

			selection.reset(attachments);

			// Set the embed state properties.
			if (isEmbed) {
				this.get('embed').props.set({
					url: audioUrl,
					title: workflow.model.get('title')
				});
			} else {
				this.get('embed').props.set({
					url: '',
					title: ''
				});
			}

			// Set the state to 'embed' if the model has an audio URL but
			// not a corresponding attachment ID.
			frame.setState(isEmbed ? 'embed' : 'insert');
		});

		// Copy data from the selected attachment to the current model.
		frame.state('insert').on('insert', function (selection) {
			var attachment = selection.first().toJSON().cue,
			    data = {},
			    keys = _.keys(workflow.model.attributes);

			// Attributes that shouldn't be updated when inserting an
			// audio attachment.
			_.without(keys, ['id', 'order']);

			// Update these attributes if they're empty.
			// They shouldn't overwrite any data entered by the user.
			_.each(keys, function (key) {
				var value = workflow.model.get(key);

				if (!value && key in attachment && value !== attachment[key]) {
					data[key] = attachment[key];
				}
			});

			// Attributes that should always be replaced.
			data.audioId = attachment.audioId;
			data.audioUrl = attachment.audioUrl;

			workflow.model.set(data);
		});

		// Copy the embed data to the current model.
		frame.state('embed').on('select', function () {
			var embed = this.props.toJSON(),
			    data = {};

			data.audioId = '';
			data.audioUrl = embed.url;

			if ('title' in embed && '' !== embed.title) {
				data.title = embed.title;
			}

			workflow.model.set(data);
		});

		// Remove an empty model if the frame is escaped.
		frame.on('escape', function () {
			var model = workflow.model.toJSON();

			if (!model.artworkUrl && !model.audioUrl) {
				workflow.model.destroy();
			}
		});

		return frame;
	}
};

module.exports = Workflows;

/***/ }),
/* 6 */
/***/ (function(module, exports) {

var g;

// This works in non-strict mode
g = (function() {
	return this;
})();

try {
	// This works if eval is allowed (see CSP)
	g = g || Function("return this")() || (1,eval)("this");
} catch(e) {
	// This works if the window reference is available
	if(typeof window === "object")
		g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;


/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var Track,
    Backbone = __webpack_require__(4);

Track = Backbone.Model.extend({
	defaults: {
		artist: '',
		artworkId: '',
		artworkUrl: '',
		audioId: '',
		audioUrl: '',
		format: '',
		length: '',
		title: '',
		order: 0
	}
});

module.exports = Track;

/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var MediaFrame,
    _ = __webpack_require__(1),
    l10n = __webpack_require__(2).l10n,
    wp = __webpack_require__(0);

MediaFrame = wp.media.view.MediaFrame.Post.extend({
	createStates: function createStates() {
		var options = this.options;

		// Add the default states.
		this.states.add([
		// Main states.
		new wp.media.controller.Library({
			id: 'insert',
			title: this.options.title,
			priority: 20,
			toolbar: 'main-insert',
			filterable: 'uploaded',
			library: wp.media.query(options.library),
			multiple: options.multiple ? 'reset' : false,
			editable: false,

			// If the user isn't allowed to edit fields,
			// can they still edit it locally?
			allowLocalEdits: true,

			// Show the attachment display settings.
			displaySettings: false,
			// Update user settings when users adjust the
			// attachment display settings.
			displayUserSettings: false
		}),

		// Embed states.
		new wp.media.controller.Embed({
			title: l10n.addFromUrl,
			menuItem: { text: l10n.addFromUrl, priority: 120 },
			type: 'link'
		})]);
	},

	bindHandlers: function bindHandlers() {
		wp.media.view.MediaFrame.Select.prototype.bindHandlers.apply(this, arguments);

		this.on('toolbar:create:main-insert', this.createToolbar, this);
		this.on('toolbar:create:main-embed', this.mainEmbedToolbar, this);

		var handlers = {
			menu: {
				'default': 'mainMenu'
			},

			content: {
				'embed': 'embedContent',
				'edit-selection': 'editSelectionContent'
			},

			toolbar: {
				'main-insert': 'mainInsertToolbar'
			}
		};

		_.each(handlers, function (regionHandlers, region) {
			_.each(regionHandlers, function (callback, handler) {
				this.on(region + ':render:' + handler, this[callback], this);
			}, this);
		}, this);
	},

	// Toolbars.
	mainInsertToolbar: function mainInsertToolbar(view) {
		var controller = this;

		this.selectionStatusToolbar(view);

		view.set('insert', {
			style: 'primary',
			priority: 80,
			text: controller.options.button.text,
			requires: {
				selection: true
			},
			click: function click() {
				var state = controller.state(),
				    selection = state.get('selection');

				controller.close();
				state.trigger('insert', selection).reset();
			}
		});
	},

	mainEmbedToolbar: function mainEmbedToolbar(toolbar) {
		toolbar.view = new wp.media.view.Toolbar.Embed({
			controller: this,
			text: this.options.button.text
		});
	}
});

module.exports = MediaFrame;

/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var AddTracksButton,
    $ = __webpack_require__(3),
    workflows = __webpack_require__(5),
    wp = __webpack_require__(0);

AddTracksButton = wp.Backbone.View.extend({
	id: 'add-tracks',
	tagName: 'p',

	events: {
		'click .button': 'click'
	},

	initialize: function initialize(options) {
		this.l10n = options.l10n;
	},

	render: function render() {
		var $button = $('<a />', {
			text: this.l10n.addTracks
		}).addClass('button button-secondary');

		this.$el.html($button);

		return this;
	},

	click: function click(e) {
		e.preventDefault();
		workflows.get('addTracks').open();
	}
});

module.exports = AddTracksButton;

/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var TrackList,
    $ = __webpack_require__(3),
    _ = __webpack_require__(1),
    Track = __webpack_require__(11),
    wp = __webpack_require__(0);

TrackList = wp.Backbone.View.extend({
	className: 'cue-tracklist',
	tagName: 'ol',

	initialize: function initialize() {
		this.listenTo(this.collection, 'add', this.addTrack);
		this.listenTo(this.collection, 'add remove', this.updateOrder);
		this.listenTo(this.collection, 'reset', this.render);
	},

	render: function render() {
		this.$el.empty();

		this.collection.each(this.addTrack, this);
		this.updateOrder();

		this.$el.sortable({
			axis: 'y',
			delay: 150,
			forceHelperSize: true,
			forcePlaceholderSize: true,
			opacity: 0.6,
			start: function start(e, ui) {
				ui.placeholder.css('visibility', 'visible');
			},
			update: _.bind(function (e, ui) {
				this.updateOrder();
			}, this)
		});

		return this;
	},

	addTrack: function addTrack(track) {
		var trackView = new Track({ model: track });
		this.$el.append(trackView.render().el);
	},

	updateOrder: function updateOrder() {
		_.each(this.$el.find('.cue-track'), function (item, i) {
			var cid = $(item).data('cid');
			this.collection.get(cid).set('order', i);
		}, this);
	}
});

module.exports = TrackList;

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var Track,
    $ = __webpack_require__(3),
    TrackArtwork = __webpack_require__(12),
    TrackAudio = __webpack_require__(13),
    wp = __webpack_require__(0);

Track = wp.Backbone.View.extend({
	tagName: 'li',
	className: 'cue-track',
	template: wp.template('cue-playlist-track'),

	events: {
		'change [data-setting]': 'updateAttribute',
		'click .js-toggle': 'toggleOpenStatus',
		'dblclick .cue-track-title': 'toggleOpenStatus',
		'click .js-close': 'minimize',
		'click .js-remove': 'destroy'
	},

	initialize: function initialize() {
		this.listenTo(this.model, 'change:title', this.updateTitle);
		this.listenTo(this.model, 'change', this.updateFields);
		this.listenTo(this.model, 'destroy', this.remove);
	},

	render: function render() {
		this.$el.html(this.template(this.model.toJSON())).data('cid', this.model.cid);

		this.views.add('.cue-track-column-artwork', new TrackArtwork({
			model: this.model,
			parent: this
		}));

		this.views.add('.cue-track-audio-group', new TrackAudio({
			model: this.model,
			parent: this
		}));

		return this;
	},

	minimize: function minimize(e) {
		e.preventDefault();
		this.$el.removeClass('is-open').find('input:focus').blur();
	},

	toggleOpenStatus: function toggleOpenStatus(e) {
		e.preventDefault();
		this.$el.toggleClass('is-open').find('input:focus').blur();

		// Trigger a resize so the media element will fill the container.
		if (this.$el.hasClass('is-open')) {
			$(window).trigger('resize');
		}
	},

	/**
  * Update a model attribute when a field is changed.
  *
  * Fields with a 'data-setting="{{key}}"' attribute whose value
  * corresponds to a model attribute will be automatically synced.
  *
  * @param {Object} e Event object.
  */
	updateAttribute: function updateAttribute(e) {
		var attribute = $(e.target).data('setting'),
		    value = e.target.value;

		if (this.model.get(attribute) !== value) {
			this.model.set(attribute, value);
		}
	},

	/**
  * Update a setting field when a model's attribute is changed.
  */
	updateFields: function updateFields() {
		var track = this.model.toJSON(),
		    $settings = this.$el.find('[data-setting]'),
		    attribute,
		    value;

		// A change event shouldn't be triggered here, so it won't cause
		// the model attribute to be updated and get stuck in an
		// infinite loop.
		for (attribute in track) {
			// Decode HTML entities.
			value = $('<div/>').html(track[attribute]).text();
			$settings.filter('[data-setting="' + attribute + '"]').val(value);
		}
	},

	updateTitle: function updateTitle() {
		var title = this.model.get('title');
		this.$el.find('.cue-track-title .text').text(title ? title : 'Title');
	},

	/**
  * Destroy the view's model.
  *
  * Avoid syncing to the server by triggering an event instead of
  * calling destroy() directly on the model.
  */
	destroy: function destroy() {
		this.model.trigger('destroy', this.model);
	},

	remove: function remove() {
		this.$el.remove();
	}
});

module.exports = Track;

/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var TrackArtwork,
    _ = __webpack_require__(1),
    workflows = __webpack_require__(5),
    wp = __webpack_require__(0);

TrackArtwork = wp.Backbone.View.extend({
	tagName: 'span',
	className: 'cue-track-artwork',
	template: wp.template('cue-playlist-track-artwork'),

	events: {
		'click': 'select'
	},

	initialize: function initialize(options) {
		this.parent = options.parent;
		this.listenTo(this.model, 'change:artworkUrl', this.render);
	},

	render: function render() {
		this.$el.html(this.template(this.model.toJSON()));
		this.parent.$el.toggleClass('has-artwork', !_.isEmpty(this.model.get('artworkUrl')));
		return this;
	},

	select: function select() {
		workflows.setModel(this.model).get('selectArtwork').open();
	}
});

module.exports = TrackArtwork;

/***/ }),
/* 13 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var TrackAudio,
    $ = __webpack_require__(3),
    _ = __webpack_require__(1),
    mejs = __webpack_require__(17),
    settings = __webpack_require__(2).settings(),
    workflows = __webpack_require__(5),
    wp = __webpack_require__(0);

TrackAudio = wp.Backbone.View.extend({
	tagName: 'span',
	className: 'cue-track-audio',
	template: wp.template('cue-playlist-track-audio'),

	events: {
		'click .cue-track-audio-selector': 'select'
	},

	initialize: function initialize(options) {
		this.parent = options.parent;

		this.listenTo(this.model, 'change:audioUrl', this.refresh);
		this.listenTo(this.model, 'destroy', this.cleanup);
	},

	render: function render() {
		var $mediaEl,
		    playerSettings,
		    track = this.model.toJSON(),
		    playerId = this.$el.find('.mejs-audio').attr('id');

		// Remove the MediaElement player object if the
		// audio file URL is empty.
		if ('' === track.audioUrl && playerId) {
			mejs.players[playerId].remove();
		}

		// Render the media element.
		this.$el.html(this.template(this.model.toJSON()));

		// Set up MediaElement.js.
		$mediaEl = this.$el.find('.cue-audio');

		if ($mediaEl.length) {
			// MediaElement traverses the DOM and throws an error if it
			// can't find a parent node before reaching <body>. It makes
			// sure the flash fallback won't exist within a <p> tag.

			// The view isn't attached to the DOM at this point, so an
			// error is thrown when reaching the top of the tree.

			// This hack makes it stop searching. The fake <body> tag is
			// removed in the success callback.
			// @see mediaelement-and-player.js:~1222
			$mediaEl.wrap('<body></body>');

			playerSettings = {
				//enablePluginDebug: true,
				classPrefix: 'mejs-',
				defaultAudioHeight: 30,
				features: ['playpause', 'current', 'progress', 'duration'],
				pluginPath: settings.pluginPath,
				stretching: 'responsive',
				success: _.bind(function (mediaElement, domObject, t) {
					var $fakeBody = $(t.container).parent();

					// Remove the fake <body> tag.
					if ($.nodeName($fakeBody.get(0), 'body')) {
						$fakeBody.replaceWith($fakeBody.get(0).childNodes);
					}
				}, this),
				error: function error(el) {
					var $el = $(el),
					    $parent = $el.closest('.cue-track'),
					    playerId = $el.closest('.mejs-audio').attr('id');

					// Remove the audio element if there is an error.
					mejs.players[playerId].remove();
					$parent.find('audio').remove();
				}
			};

			// Hack to allow .m4a files.
			// @link https://github.com/johndyer/mediaelement/issues/291
			if ('m4a' === $mediaEl.attr('src').split('.').pop()) {
				playerSettings.pluginVars = 'isvideo=true';
			}

			$mediaEl.mediaelementplayer(playerSettings);
		}

		return this;
	},

	refresh: function refresh(e) {
		var track = this.model.toJSON(),
		    playerId = this.$el.find('.mejs-audio').attr('id'),
		    player = playerId ? mejs.players[playerId] : null;

		if (player && '' !== track.audioUrl) {
			player.pause();
			player.setSrc(track.audioUrl);
		} else {
			this.render();
		}
	},

	cleanup: function cleanup() {
		var playerId = this.$el.find('.mejs-audio').attr('id'),
		    player = playerId ? mejs.players[playerId] : null;

		if (player) {
			player.remove();
		}
	},

	select: function select() {
		workflows.setModel(this.model).get('selectAudio').open();
	}
});

module.exports = TrackAudio;

/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/*global _cueSettings:false */

var $ = __webpack_require__(3),
    cue = __webpack_require__(2),
    wp = __webpack_require__(0);

cue.data = _cueSettings; // Back-compat.
cue.settings(_cueSettings);

wp.media.view.settings.post.id = cue.data.postId;
wp.media.view.settings.defaultProps = {};

cue.model.Track = __webpack_require__(7);
cue.model.Tracks = __webpack_require__(15);

cue.view.MediaFrame = __webpack_require__(8);
cue.view.PostForm = __webpack_require__(16);
cue.view.AddTracksButton = __webpack_require__(9);
cue.view.TrackList = __webpack_require__(10);
cue.view.Track = __webpack_require__(11);
cue.view.TrackArtwork = __webpack_require__(12);
cue.view.TrackAudio = __webpack_require__(13);

cue.workflows = __webpack_require__(5);

/**
 * ========================================================================
 * SETUP
 * ========================================================================
 */

$(function ($) {
	var tracks;

	tracks = cue.tracks = new cue.model.Tracks(cue.data.tracks);
	delete cue.data.tracks;

	new cue.view.PostForm({
		collection: tracks,
		l10n: cue.l10n
	});
});

/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var Tracks,
    _ = __webpack_require__(1),
    Backbone = __webpack_require__(4),
    settings = __webpack_require__(2).settings(),
    Track = __webpack_require__(7),
    wp = __webpack_require__(0);

Tracks = Backbone.Collection.extend({
	model: Track,

	comparator: function comparator(track) {
		return parseInt(track.get('order'), 10);
	},

	fetch: function fetch() {
		var collection = this;

		return wp.ajax.post('cue_get_playlist_tracks', {
			post_id: settings.postId
		}).done(function (tracks) {
			collection.reset(tracks);
		});
	},

	save: function save(data) {
		this.sort();

		data = _.extend({}, data, {
			post_id: settings.postId,
			tracks: this.toJSON(),
			nonce: settings.saveNonce
		});

		return wp.ajax.post('cue_save_playlist_tracks', data);
	}
});

module.exports = Tracks;

/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var PostForm,
    $ = __webpack_require__(3),
    AddTracksButton = __webpack_require__(9),
    TrackList = __webpack_require__(10),
    wp = __webpack_require__(0);

PostForm = wp.Backbone.View.extend({
	el: '#post',
	saved: false,

	events: {
		'click #publish': 'buttonClick',
		'click #save-post': 'buttonClick'
		//'submit': 'submit'
	},

	initialize: function initialize(options) {
		this.l10n = options.l10n;

		this.render();
	},

	render: function render() {
		this.views.add('#cue-playlist-editor .cue-panel-body', [new AddTracksButton({
			collection: this.collection,
			l10n: this.l10n
		}), new TrackList({
			collection: this.collection
		})]);

		return this;
	},

	buttonClick: function buttonClick(e) {
		var self = this,
		    $button = $(e.target);

		if (!self.saved) {
			this.collection.save().done(function (data) {
				self.saved = true;
				$button.click();
			});
		}

		return self.saved;
	}
});

module.exports = PostForm;

/***/ }),
/* 17 */
/***/ (function(module, exports) {

module.exports = mejs;

/***/ })
/******/ ]);
