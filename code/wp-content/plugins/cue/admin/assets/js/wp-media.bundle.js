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
/******/ 	return __webpack_require__(__webpack_require__.s = 18);
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
/* 5 */,
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
/* 7 */,
/* 8 */,
/* 9 */,
/* 10 */,
/* 11 */,
/* 12 */,
/* 13 */,
/* 14 */,
/* 15 */,
/* 16 */,
/* 17 */,
/* 18 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


/*global _cueMediaSettings:false, wp:false */

(function (wp) {
	'use strict';

	var cue = __webpack_require__(2);

	cue.settings(_cueMediaSettings);

	wp.media.view.MediaFrame.Post = __webpack_require__(19);
})(wp);

/***/ }),
/* 19 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var InsertPlaylistFrame,
    PlaylistBrowser = __webpack_require__(20),
    PlaylistsController = __webpack_require__(25),
    PlaylistToolbar = __webpack_require__(26),
    wp = __webpack_require__(0),
    PostFrame = wp.media.view.MediaFrame.Post;

InsertPlaylistFrame = PostFrame.extend({
	createStates: function createStates() {
		PostFrame.prototype.createStates.apply(this, arguments);

		this.states.add(new PlaylistsController({}));
	},

	bindHandlers: function bindHandlers() {
		PostFrame.prototype.bindHandlers.apply(this, arguments);

		//this.on( 'menu:create:default', this.createCueMenu, this );
		this.on('content:create:cue-playlist-browser', this.createCueContent, this);
		this.on('toolbar:create:cue-insert-playlist', this.createCueToolbar, this);
	},

	createCueMenu: function createCueMenu(menu) {
		menu.view.set({
			'cue-playlist-separator': new wp.media.View({
				className: 'separator',
				priority: 200
			})
		});
	},

	createCueContent: function createCueContent(content) {
		content.view = new PlaylistBrowser({
			controller: this
		});
	},

	createCueToolbar: function createCueToolbar(toolbar) {
		toolbar.view = new PlaylistToolbar({
			controller: this
		});
	}
});

module.exports = InsertPlaylistFrame;

/***/ }),
/* 20 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var PlaylistBrowser,
    _ = __webpack_require__(1),
    PlaylistItems = __webpack_require__(21),
    PlaylistNoItems = __webpack_require__(23),
    PlaylistSidebar = __webpack_require__(24),
    wp = __webpack_require__(0);

PlaylistBrowser = wp.Backbone.View.extend({
	className: 'cue-playlist-browser',

	initialize: function initialize(options) {
		this.collection = options.controller.state().get('collection');
		this.controller = options.controller;

		this._paged = 1;
		this._pending = false;

		_.bindAll(this, 'scroll');
		this.listenTo(this.collection, 'reset', this.render);

		if (!this.collection.length) {
			this.getPlaylists();
		}
	},

	render: function render() {
		this.$el.off('scroll').on('scroll', this.scroll);

		this.views.add([new PlaylistItems({
			collection: this.collection,
			controller: this.controller
		}), new PlaylistSidebar({
			controller: this.controller
		}), new PlaylistNoItems({
			collection: this.collection
		})]);

		return this;
	},

	scroll: function scroll() {
		if (!this._pending && this.el.scrollHeight < this.el.scrollTop + this.el.clientHeight * 3) {
			this._pending = true;
			this.getPlaylists();
		}
	},

	getPlaylists: function getPlaylists() {
		var view = this;

		wp.ajax.post('cue_get_playlists', {
			paged: view._paged
		}).done(function (response) {
			view.collection.add(response.playlists);

			view._paged++;

			if (view._paged <= response.maxNumPages) {
				view._pending = false;
				view.scroll();
			}
		});
	}
});

module.exports = PlaylistBrowser;

/***/ }),
/* 21 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var PlaylistItems,
    PlaylistItem = __webpack_require__(22),
    wp = __webpack_require__(0);

PlaylistItems = wp.Backbone.View.extend({
	className: 'cue-playlist-browser-list',
	tagName: 'ul',

	initialize: function initialize(options) {
		this.collection = options.controller.state().get('collection');
		this.controller = options.controller;

		this.listenTo(this.collection, 'add', this.addItem);
		this.listenTo(this.collection, 'reset', this.render);
	},

	render: function render() {
		this.collection.each(this.addItem, this);
		return this;
	},

	addItem: function addItem(model) {
		var view = new PlaylistItem({
			controller: this.controller,
			model: model
		}).render();

		this.$el.append(view.el);
	}
});

module.exports = PlaylistItems;

/***/ }),
/* 22 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var Playlist,
    wp = __webpack_require__(0);

Playlist = wp.Backbone.View.extend({
	tagName: 'li',
	className: 'cue-playlist-browser-list-item',
	template: wp.template('cue-playlist-browser-list-item'),

	events: {
		'click': 'resetSelection'
	},

	initialize: function initialize(options) {
		this.controller = options.controller;
		this.model = options.model;
		this.selection = this.controller.state().get('selection');

		this.listenTo(this.selection, 'add remove reset', this.updateSelectedClass);
	},

	render: function render() {
		this.$el.html(this.template(this.model.toJSON()));
		return this;
	},

	resetSelection: function resetSelection(e) {
		if (this.selection.contains(this.model)) {
			this.selection.remove(this.model);
		} else {
			this.selection.reset(this.model);
		}
	},

	updateSelectedClass: function updateSelectedClass() {
		if (this.selection.contains(this.model)) {
			this.$el.addClass('is-selected');
		} else {
			this.$el.removeClass('is-selected');
		}
	}
});

module.exports = Playlist;

/***/ }),
/* 23 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var PlaylistNoItems,
    wp = __webpack_require__(0);

PlaylistNoItems = wp.Backbone.View.extend({
	className: 'cue-playlist-browser-empty',
	tagName: 'div',
	template: wp.template('cue-playlist-browser-empty'),

	initialize: function initialize(options) {
		this.collection = this.collection;

		this.listenTo(this.collection, 'add remove reset', this.toggleVisibility);
	},

	render: function render() {
		this.$el.html(this.template());
		return this;
	},

	toggleVisibility: function toggleVisibility() {
		this.$el.toggleClass('is-visible', this.collection.length < 1);
	}
});

module.exports = PlaylistNoItems;

/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var PlaylistSidebar,
    $ = __webpack_require__(3),
    wp = __webpack_require__(0);

PlaylistSidebar = wp.Backbone.View.extend({
	className: 'cue-playlist-browser-sidebar media-sidebar',
	template: wp.template('cue-playlist-browser-sidebar'),

	events: {
		'change [data-setting]': 'updateAttribute'
	},

	initialize: function initialize(options) {
		this.attributes = options.controller.state().get('attributes');
	},

	render: function render() {
		this.$el.html(this.template());
	},

	updateAttribute: function updateAttribute(e) {
		var $target = $(e.target),
		    attribute = $target.data('setting'),
		    value = e.target.value;

		if ('checkbox' === e.target.type) {
			value = !!$target.prop('checked');
		}

		this.attributes.set(attribute, value);
	}
});

module.exports = PlaylistSidebar;

/***/ }),
/* 25 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var Playlists,
    Backbone = __webpack_require__(4),
    l10n = __webpack_require__(2).l10n,
    wp = __webpack_require__(0);

Playlists = wp.media.controller.State.extend({
	defaults: {
		id: 'cue-playlists',
		title: l10n.insertPlaylist || 'Insert Playlist',
		collection: null,
		content: 'cue-playlist-browser',
		menu: 'default',
		menuItem: {
			text: l10n.insertFromCue || 'Insert from Cue',
			priority: 130
		},
		selection: null,
		toolbar: 'cue-insert-playlist'
	},

	initialize: function initialize(options) {
		var collection = options.collection || new Backbone.Collection(),
		    selection = options.selection || new Backbone.Collection();

		this.set('attributes', new Backbone.Model({
			id: null,
			show_playlist: true
		}));

		this.set('collection', collection);
		this.set('selection', selection);

		this.listenTo(selection, 'remove', this.updateSelection);
	}
});

module.exports = Playlists;

/***/ }),
/* 26 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


var PlaylistToolbar,
    _ = __webpack_require__(1),
    wp = __webpack_require__(0);

PlaylistToolbar = wp.media.view.Toolbar.extend({
	initialize: function initialize(options) {
		this.controller = options.controller;

		_.bindAll(this, 'insertCueShortcode');

		// This is a button.
		this.options.items = _.defaults(this.options.items || {}, {
			insert: {
				text: wp.media.view.l10n.insertIntoPost || 'Insert into post',
				style: 'primary',
				priority: 80,
				requires: {
					selection: true
				},
				click: this.insertCueShortcode
			}
		});

		wp.media.view.Toolbar.prototype.initialize.apply(this, arguments);
	},

	insertCueShortcode: function insertCueShortcode() {
		var html,
		    state = this.controller.state(),
		    attributes = state.get('attributes').toJSON(),
		    selection = state.get('selection').first();

		attributes.id = selection.get('id');
		_.pick(attributes, 'id', 'theme', 'width', 'show_playlist');

		if (!attributes.show_playlist) {
			attributes.show_playlist = '0';
		} else {
			delete attributes.show_playlist;
		}

		html = wp.shortcode.string({
			tag: 'cue',
			type: 'single',
			attrs: attributes
		});

		wp.media.editor.insert(html);
		this.controller.close();
	}
});

module.exports = PlaylistToolbar;

/***/ })
/******/ ]);
