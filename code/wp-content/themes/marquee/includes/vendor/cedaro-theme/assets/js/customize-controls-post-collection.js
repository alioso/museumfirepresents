/* global _, Backbone, wp */

(function( window, $, _, Backbone, wp, undefined ) {
	'use strict';

	// @todo Make post meta handling more generic.

	var api = wp.customize,
		app = {};

	_.extend( app, { model: {}, view: {} } );

	function getPostMetaSettingId( postId, metaKey ) {
		return 'cedaro_theme_postmeta[' + postId + '][' + metaKey + ']';
	}

	function updatePostMeta( postId, metaKey, value ) {
		var setting,
			settingId = getPostMetaSettingId( postId, metaKey );

		if ( api.has( settingId ) ) {
			api( settingId ).set( value );
		} else {
			setting = new api.Setting( settingId, value, {
				transport: 'refresh',
				previewer: api.previewer,
				dirty: true
			});

			api.add( settingId, setting );

			// Update the dirty state.
			api.state( 'saved' ).set( false );

			// Refresh the previewer.
			if ( 'postMessage' !== setting.transport ) {
				api.previewer.refresh();
			}
		}
	}

	/**
	 * ========================================================================
	 * MODELS
	 * ========================================================================
	 */

	app.model.Post = Backbone.Model.extend({
		defaults: {
			fields: [],
			sortableOrder: 0,
			title: ''
		},

		initialize: function( attributes, options ) {
			this.meta = new Backbone.Model( attributes.meta || {} );
			this.meta.on( 'change', this.metaChanged, this );
		},

		metaChanged: function( model ) {
			this.trigger( 'meta:change', this, model );
		}
	});

	app.model.Posts = Backbone.Collection.extend({
		model: app.model.Post,
		comparator: 'sortableOrder'
	});

	/**
	 * ========================================================================
	 * VIEWS
	 * ========================================================================
	 */

	app.view.DrawerTitle = wp.Backbone.View.extend({
		className: 'ctpc-drawer-title customize-section-title',
		template: wp.template( 'ctpc-drawer-title' ),

		events: {
			'click .customize-section-back': 'collapseDrawer'
		},

		initialize: function( options ) {
			this.drawer = options.drawer;
		},

		render: function() {
			this.$el.html( this.template( this.drawer.labels ) );
			return this;
		},

		collapseDrawer: function( e ) {
			e.preventDefault();
			this.drawer.collapse();
		}
	});

	app.view.DrawerNotice = wp.Backbone.View.extend({
		tagName: 'div',
		className: 'ctpc-drawer-notice',

		initialize: function( options ) {
			this.drawer = options.drawer;
			this.listenTo( this.drawer.state, 'change:notice', this.render );
		},

		render: function() {
			var notice = this.drawer.state.get( 'notice' );
			this.$el.toggle( !! notice.length ).text( notice );
			return this;
		}
	});

	app.view.SearchForm = wp.Backbone.View.extend({
		tagName: 'div',
		className: 'ctpc-search-group',
		template: wp.template( 'ctpc-search-group' ),

		events: {
			'click .clear-results' : 'clearResults',
			'input input': 'search'
		},

		initialize: function( options ) {
			this.collection = options.collection;
			this.drawer = options.drawer;

			this.listenTo( this.collection, 'add remove reset', this.updateClearResultsVisibility );
		},

		render: function() {
			this.$el.html( this.template({ labels: this.drawer.labels }) );
			this.$clearResults = this.$( '.clear-results' );
			this.$field = this.$( '.ctpc-search-group-field' );
			this.$spinner = this.$el.append( '<span class="ctpc-search-group-spinner spinner" />' ).find( '.spinner' );
			this.updateClearResultsVisibility();
			return this;
		},

		clearResults: function() {
			this.collection.reset();
			this.$field.val( '' ).trigger( 'input' ).focus();
		},

		search: function() {
			var view = this;

			this.$el.addClass( 'is-searching' );
			this.$spinner.addClass( 'is-active' );

			clearTimeout( this.timeout );
			this.timeout = setTimeout(function() {
				view.drawer.search( view.$field.val() )
					.always(function() {
						view.$el.removeClass( 'is-searching' );
						view.$spinner.removeClass( 'is-active' );
					});
			}, 300 );
		},

		updateClearResultsVisibility: function() {
			this.$clearResults.toggleClass( 'is-visible', !! this.collection.length && '' !== this.$field.val() );
		}
	});

	app.view.SearchResults = wp.Backbone.View.extend({
		tagName: 'div',
		className: 'ctpc-search-results',

		initialize: function( options ) {
			this.collection = options.collection;
			this.drawer = options.drawer;
			this.selection = options.selection;

			this.listenTo( this.collection, 'reset', this.render );
		},

		render: function() {
			this.$list = this.$el.html( '<ul />' ).find( 'ul' );
			this.$el.toggleClass( 'hide-type-label', 1 === this.drawer.postTypes.length );

			if ( this.collection.length ) {
				this.collection.each( this.addItem, this );
			} else {
				this.$el.empty();
			}

			return this;
		},

		addItem: function( model ) {
			this.views.add( 'ul', new app.view.SearchResult({
				drawer: this.drawer,
				model: model,
				selection: this.selection
			}));
		}
	});

	app.view.SearchResult = wp.Backbone.View.extend({
		tagName: 'li',
		className: 'ctpc-search-results-item',
		template: wp.template( 'ctpc-search-result' ),

		events: {
			'click': 'addItem'
		},

		initialize: function( options ) {
			this.drawer = options.drawer;
			this.model = options.model;
			this.selection = options.selection;

			this.listenTo( this.selection, 'add remove reset', this.updateSelectedClass );
		},

		render: function() {
			var data = _.extend( this.model.toJSON(), {
				labels: this.drawer.labels
			});

			this.$el.html( this.template( data ) );
			this.updateSelectedClass();

			return this;
		},

		addItem: function() {
			this.selection.add( this.model );
		},

		updateSelectedClass: function() {
			this.$el.toggleClass( 'is-selected', !! this.selection.get( this.model.id ) );
		}
	});

	app.view.AddNewItemButton = wp.Backbone.View.extend({
		className: 'ctpc-add-new-item button button-secondary alignright',
		tagName: 'button',

		events: {
			click: 'toggleDrawer'
		},

		initialize: function( options ) {
			this.control = options.control;
		},

		render: function() {
			this.$el.text( this.control.params.labels.addPosts );
			return this;
		},

		toggleDrawer: function( e ) {
			e.preventDefault();
			this.control.drawer.toggle();
		}
	});

	app.view.ItemList = wp.Backbone.View.extend({
		className: 'ctpc-items-list',
		tagName: 'ol',

		initialize: function( options ) {
			var view = this;

			this.control = options.control;

			this.listenTo( this.collection, 'add', this.addItem );
			this.listenTo( this.collection, 'add remove', this.updateOrder );
			this.listenTo( this.collection, 'reset', this.render );
		},

		render: function() {
			this.$el.empty();
			this.collection.each( this.addItem, this );
			this.initializeSortable();
			return this;
		},

		initializeSortable: function() {
			this.$el.sortable({
				axis: 'y',
				delay: 150,
				forceHelperSize: true,
				forcePlaceholderSize: true,
				opacity: 0.6,
				start: function( e, ui ) {
					ui.placeholder.css( 'visibility', 'visible' );
				},
				update: _.bind(function() {
					this.updateOrder();
				}, this )
			});
		},

		addItem: function( item ) {
			var itemView = new app.view.Item({
				control: this.control,
				model: item,
				parent: this
			});

			this.$el.append( itemView.render().el );
		},

		updateOrder: function() {
			_.each( this.$el.children(), function( item, index ) {
				var id = $( item ).data( 'post-id' );
				this.collection.get( id ).set( 'sortableOrder', index );
			}, this );

			this.collection.sort();
		}
	});

	app.view.Item = wp.Backbone.View.extend({
		tagName: 'li',
		className: 'ctpc-item',
		template: wp.template( 'ctpc-item' ),

		events: {
			'click .js-toggle': 'toggleOpenStatus',
			'dblclick .ctpc-item-title': 'toggleOpenStatus',
			'click .js-close': 'minimize',
			'click .js-remove': 'destroy'
		},

		initialize: function( options ) {
			this.control = options.control;
			this.parent = options.parent;
			this.listenTo( this.model, 'destroy', this.remove );
			this.listenTo( this.model, 'change:custom_title', this.updateTitle );
		},

		render: function() {
			var data,
				view = this,
				views = [];

			data = _.extend( this.model.toJSON(), {
				labels: this.control.params.labels
			});

			this.$el.html( this.template( data ) );
			this.$el.data( 'post-id', this.model.get( 'id' ) );
			this.$title = this.$( '.ctpc-item-title span' );

			_.each( this.model.get( 'fields' ), function( field, key ) {
				var options = {
						control: view.control,
						field: field,
						label: field.label,
						metaKey: field.key,
						model: view.model
					};

				if ( 'color' === field.type ) {
					views.push( new app.view.ItemColorField( options ) );
				} else if ( 'image' === field.type ) {
					options.imageUrl = field.imageUrl;
					views.push( new app.view.ItemImageField( options ) );
				} else if ( 'select' === field.type ) {
					options.choices = field.choices;
					views.push( new app.view.ItemSelectField( options ) );
				} else if ( 'text' === field.type ) {
					views.push( new app.view.ItemField( options ) );
				} else if ( 'title' === field.type ) {
					views.push( new app.view.ItemTitleField( options ) );
				}
			});

			if ( views.length > 0 ) {
				this.$el.addClass( 'has-content' );
				this.views.set( '.ctpc-item-body', views );
			}

			return this;
		},

		minimize: function( e ) {
			e.preventDefault();
			this.$el.removeClass( 'is-open' );
		},

		toggleOpenStatus: function( e ) {
			e.preventDefault();
			this.$el.toggleClass( 'is-open' );
		},

		/**
		 * Destroy the view's model.
		 *
		 * Avoid syncing to the server by triggering an event instead of
		 * calling destroy() directly on the model.
		 */
		destroy: function() {
			this.model.trigger( 'destroy', this.model );
		},

		remove: function() {
			this.$el.remove();
		},

		updateTitle: function() {
			var custom = this.model.get( 'custom_title' ),
				title = this.model.get( 'title' );

			this.$title.text( custom || title );
		}
	});

	app.view.ItemField = wp.Backbone.View.extend({
		tagName: 'p',
		className: 'ctpc-item-meta',
		template: wp.template( 'ctpc-item-field' ),

		events: {
			'change input': 'updateMeta',
			'input input': 'updateMeta'
		},

		initialize: function( options ) {
			this.label = options.label;
			this.metaKey = options.metaKey;
			this.model = options.model;
		},

		render: function() {
			this.$el.html( this.template({
				label: this.label,
				value: this.model.meta.get( this.metaKey )
			}) );

			this.$field = this.$( 'input' );

			return this;
		},

		updateMeta: function() {
			this.model.meta.set( this.metaKey, this.$field.val() );
		}
	});

	app.view.ItemColorField = wp.Backbone.View.extend({
		tagName: 'p',
		className: 'ctpc-item-meta',
		template: wp.template( 'ctpc-item-field' ),

		events: {
			'change input': 'updateMeta',
			'input input': 'updateMeta'
		},

		initialize: function( options ) {
			this.field = options.field;
			this.label = options.label;
			this.metaKey = options.metaKey;
			this.model = options.model;
		},

		render: function() {
			this.$el.html( this.template({
				label: this.label,
				value: this.model.meta.get( this.metaKey )
			}) );

			this.$field = this.$( 'input' );

			this.colorPicker = this.$field.wpColorPicker({
				defaultColor: this.field.default || false,
				change: function( e, options ) {
					$( this ).val( options.color.toCSS() ).trigger( 'change' );
				},
				clear: function() {
					$( this ).trigger( 'change' );
				}
			});

			return this;
		},

		updateMeta: function() {
			this.model.meta.set( this.metaKey, this.$field.val() );
		}
	});

	app.view.ItemImageField = wp.Backbone.View.extend({
		tagName: 'p',
		className: 'ctpc-item-image',

		events: {
			'click': 'openMediaManager'
		},

		initialize: function( options ) {
			this.control = options.control;
			this.labels = this.control.params.labels;
			this.metaKey = options.metaKey;
			this.model = options.model;
			this.imageUrl = options.imageUrl;

			this.listenTo( this.model.meta, 'change:' + this.metaKey, this.render );
		},

		render: function() {
			if ( this.imageUrl ) {
				this.$el.html( $( '<img />', { src: this.imageUrl }) );

				this.views.add([
					new app.view.ItemImageFieldRemoveButton({
						parent: this
					})
				]);
			} else {
				this.$el.empty();
			}

			return this;
		},

		frame: function() {
			var view = this;

			if ( this._frame ) {
				return this._frame;
			}

			this._frame = wp.media({
				title: this.labels.featuredImage,
				library: {
					type: 'image'
				},
				button: {
					text: this.labels.setFeaturedImage
				},
				multiple: false
			});

			// Automatically select the existing image if possible.
			this._frame.on( 'open', function() {
				var selection = this.get( 'library' ).get( 'selection' ),
					attachmentId = view.model.meta.get( view.metaKey ),
					attachments = [];

				if ( attachmentId ) {
					attachments.push( wp.media.model.Attachment.get( attachmentId ) );
					attachments[0].fetch();
				}

				selection.reset( attachments );
			});

			this._frame.state( 'library' ).on( 'select', function() {
				var imageUrl, size,
					attachment = this.get( 'selection' ).first().toJSON(),
					sizes = attachment.sizes;

				size = sizes['post-thumbnail'] || sizes.medium;

				view.imageUrl = size ? size.url : attachment.url;
				view.model.meta.set( view.metaKey, attachment.id );
			});

			return this._frame;
		},

		openMediaManager: function( e ) {
			e.preventDefault();
			this.frame().open();
		}
	});

	app.view.ItemImageFieldRemoveButton = wp.Backbone.View.extend({
		tagName: 'button',
		className: 'ctpc-item-image-remove dashicons dashicons-no close',

		events: {
			'click': 'removeImage'
		},

		initialize: function( options ) {
			this.parent = options.parent;
			this.labels = this.parent.control.params.labels;
		},

		render: function() {
			this.$el.html( $( '<span />', {
				'class': 'screen-reader-text',
				'text': this.labels.remove || 'Remove'
			}) );
			return this;
		},

		removeImage: function( e ) {
			e.preventDefault();
			e.stopPropagation();

			this.parent.imageUrl = '';
			this.parent.model.meta.set( this.parent.metaKey, 0 );
		}
	});

	app.view.ItemSelectField = wp.Backbone.View.extend({
		tagName: 'p',
		className: 'ctpc-item-meta',
		template: wp.template( 'ctpc-item-select-field' ),

		events: {
			'change select': 'updateMeta'
		},

		initialize: function( options ) {
			this.choices = options.choices;
			this.label = options.label;
			this.metaKey = options.metaKey;
			this.model = options.model;
		},

		render: function() {
			this.$el.html( this.template({
				choices: this.choices,
				label: this.label,
				value: this.model.meta.get( this.metaKey )
			}) );

			this.$field = this.$( 'select' );

			return this;
		},

		updateMeta: function() {
			this.model.meta.set( this.metaKey, this.$field.val() );
		}
	});

	app.view.ItemTitleField = app.view.ItemField.extend({
		tagName: 'p',
		className: 'ctpc-item-meta',
		template: wp.template( 'ctpc-item-field' ),

		events: {
			'input input': 'updateTitle'
		},

		initialize: function( options ) {
			this.field = options.field;
			this.label = options.label;
			this.metaKey = options.metaKey;
			this.model = options.model;
		},

		render: function() {
			this.$el.html( this.template({
				label: this.label,
				value: this.model.meta.get( this.metaKey )
			}) );

			this.$field = this.$( 'input' );
			this.updateTitle();

			return this;
		},

		updateTitle: function() {
			var value = this.$field.val();
			this.model.meta.set( this.metaKey, value );
			this.model.set( 'custom_title', value );

			if ( 'hideKey' in this.field ) {
				this.model.meta.set( this.field.hideKey, '' === value ? 'yes' : 'no' );
			}
		}
	});

	/**
	 * ========================================================================
	 * Customizer Objects
	 * ========================================================================
	 */

	app.Drawer = api.Class.extend({
		type: 'drawer',

		initialize: function( id, options ) {
			var drawer = this;

			_.extend( this, options || {} );
			this.id = id;

			_.bindAll( this, 'collapseOtherDrawers' );
			this.container = $( '<div class="ctpc-drawer" />' );

			this.deferred = {
				embedded: new $.Deferred()
			};

			this.control = new api.Value();
			this.control.set( options.control );

			this.expanded = new api.Value();
			this.expanded.set( false );
			this.expanded.bind( this.collapseOtherDrawers );

			drawer.embed();
			drawer.deferred.embedded.done(function () {
				drawer.ready();
			});

			// Collapse the drawer when the control's section is collapsed.
			api.control( this.control(), function( control ) {
				api.section( control.section() ).expanded.bind(function( isExpanded ) {
					if ( ! isExpanded ) {
						drawer.collapse();
					}
				});
			});
		},

		embed: function () {
			$( '.wp-full-overlay' ).append( this.container );

			this.view = new wp.Backbone.View({
				el: this.container
			});

			this.view.views.add(
				new app.view.DrawerTitle({
					drawer: this
				})
			);

			this.deferred.embedded.resolve();
		},

		ready: function() {},

		collapse: function() {
			this.expanded.set( false );
			this.container.removeClass( 'is-open' );
			$( document.body ).removeClass( 'drawer-is-open' );
			api.control( this.control() ).container.removeClass( 'is-drawer-open' );
		},

		expand: function() {
			this.expanded.set( true );
			this.container.addClass( 'is-open' );
			$( document.body ).addClass( 'drawer-is-open' );
			api.control( this.control() ).container.addClass( 'is-drawer-open' );
		},

		toggle: function() {
			if ( this.expanded() ) {
				this.collapse();
			} else {
				this.expand();
			}
		},

		collapseOtherDrawers: function( isExpanded ) {
			if ( isExpanded ) {
				app.drawer.each(function( drawer ) {
					if ( drawer.id !== this.id ) {
						drawer.collapse();
					}
				}, this );

				if ( this.expanded() ) {
					$( document.body ).addClass( 'drawer-is-open' );
				}
			}
		}
	});

	app.PostSearchDrawer = app.Drawer.extend({
		type: 'search-drawer',

		ready: function() {
			var drawer = this;

			this.results = new app.model.Posts();

			this.state = new Backbone.Model({
				notice: ''
			});

			this.view.views.add([
				new app.view.SearchForm({
					collection: this.results,
					drawer: this
				}),
				new app.view.DrawerNotice({
					drawer: this
				}),
				new app.view.SearchResults({
					collection: this.results,
					drawer: this,
					selection: this.selection
				})
			]);

			this.expanded.bind(function( isExpanded ) {
				if ( isExpanded && drawer.results.length < 1 ) {
					drawer.search();
				}
			});
		},

		search: function( query ) {
			var drawer = this;

			return wp.ajax.post( 'ctpc_find_posts', {
				s: query,
				post_types: this.postTypes,
				post_status: 'publish',
				collection_id: this.id,
				_ajax_nonce: this.searchNonce
			}).done(function( response ) {
				drawer.results.reset( response );
				drawer.state.set( 'notice', '' );
			}).fail(function( response ) {
				drawer.results.reset();
				drawer.state.set( 'notice', response );
			});
		}
	})

	app.PostCollectionControl = api.Control.extend({
		ready: function() {
			var control = this;

			this.posts = new app.model.Posts( this.params.posts );
			delete this.params.posts;

			this.drawer = new app.PostSearchDrawer( this.id, {
				control: this.id,
				labels: _.extend( this.params.labels, {
					customizeAction: this.params.labels.addPosts,
					title: this.params.label
				}),
				postTypes: this.params.postTypes,
				searchNonce: this.params.searchNonce,
				selection: this.posts
			});
			app.drawer.add( this.id, this.drawer );

			this.view = new wp.Backbone.View({
				el: this.container
			});

			this.view.views.add([
				new app.view.ItemList({
					collection: this.posts,
					control: this
				}),
				new app.view.AddNewItemButton({
					control: this
				})
			]);

			// Update the setting when the post collection is modified.
			this.posts.on( 'add remove reset sort', function() {
				var ids = this.posts.pluck( 'id' );

				if ( this.setting() !== ids ) {
					this.setting.set( ids );
				}
			}, this );

			this.posts.on( 'meta:change', function( model ) {
				_.each( model.meta.changedAttributes(), function( value, metaKey ) {
					updatePostMeta( model.get( 'id' ), metaKey, value );
				});
			});

			// Remove post meta Customizer settings for removed posts.
			this.posts.on( 'remove', function( model ) {
				var postId = model.get( 'id' );

				_.each( model.meta.attributes, function( value, metaKey ) {
					var settingId = getPostMetaSettingId( postId, metaKey );
					api.remove( settingId );
				});
			});
		}
	});

	/**
	 * ========================================================================
	 * SETUP
	 * ========================================================================
	 */

	/**
	 * Create the collection for Drawers.
	 */
	app.drawer = new api.Values({ defaultConstructor: app.Drawer });

	/**
	 * Extends wp.customize.controlConstructor with control constructor for
	 * post_collection.
	 */
	$.extend( api.controlConstructor, {
		'cedaro-theme-post-collection': app.PostCollectionControl
	});

})( window, jQuery, _, Backbone, wp );
