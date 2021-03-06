=== Custom Post Limits ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: posts, archives, listing, limit, query, front page, categories, tags, coffee2code
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.1
Tested up to: 4.7
Stable tag: 4.0.2

Independently control the number of posts listed on the front page, author/category/tag archives, search results, etc.


== Description ==

Control the number of posts that appear on the front page, search results, and author, category, tag, and date archives, independent of each other, including specific archives.

By default, WordPress provides a single configuration setting to control how many posts should be listed in each section of your blog. This value applies for the front page listing, author listings, archive listings, category listings, tag listings, and search results. This plugin allows you to override that value for each of those different sections.

Specifically, this plugin allows you to define limits for:

* Authors archives (the archive listing of posts for any author)
* Authors archives non-first-page (when paging through authors archives listings, number of posts listed when not on the first page)
* Author archives (the archive listing of posts for any specific author)
* Categories archives (the archive listings of posts for any category)
* Categories archives non-first-page (when paging through categories archives listings, number of posts listed when not on the first page)
* Category archive (the archive listings of posts for any specific category)
* Day archives (the archive listings of posts for any day)
* Day archives non-first-page (when paging through day archives listings, number of posts listed when not on the first page)
* Front page (the listing of posts on the front page of the blog)
* Front page non-first-page (when paging through front page listings, number of posts listed when not on the first page)
* Month archives (the archive listings of posts for any month)
* Month archives non-first-page (when paging through month archives listings, number of posts listed when not on the first page)
* Search results (the listing of search results)
* Search results non-first-page (when paging through search results listings, number of posts listed when not on the first page)
* Tags archives (the archive listings of posts for any tag)
* Tags archives non-first-page (when paging through tags archives listings, number of posts listed when not on the first page)
* Tag archive (the archive listings of posts for any specific tag)
* Year archives (the archive listings of posts for any year)
* Year archives non-first-page (when paging through year archives listings, number of posts listed when not on the first page)

If the limit field is empty or 0 for a particular section type, then the default post limit will apply. If the value is set to -1, then there will be NO limit for that section (meaning ALL posts will be shown). The Archives Limit value is also treated as the default limit for Day, Month, and Year archives, unless those are explicitly defined.

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/custom-post-limits/) | [Plugin Directory Page](https://wordpress.org/plugins/custom-post-limits/) | [Author Homepage](http://coffee2code.com)


== Installation ==

1. Whether installing or updating, whether this plugin or any other, it is always advisable to back-up your data before starting
1. Install via the built-in WordPress plugin installer. Or download and unzip `custom-post-limits.zip` inside the plugins directory for your site (typically `/wp-content/plugins/`).
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. Click the plugin's `Settings` link next to its `Deactivate` link (still on the Plugins page), or click on the `Settings` -> `Post Limits` link, to go to the plugin's admin settings page. Optionally customize the limits.


== Frequently Asked Questions ==

= Does this plugin introduce additional database queries (or excessively burden the primary query) to achieve its ends? =

No. The plugin filters the posts_per_page setting value (and, when necessary, the LIMIT SQL clause) as used by the primary WordPress post query as appropriate, resulting in retrieval of only the number of posts up to the limit you specified without significant alteration of the primary query itself and without additional queries. Bottom line: this should perform efficiently.

= Is this plugin unit-tested? =

Yes.


== Screenshots ==

1. A screenshot of the plugin's admin settings page (with individual categories limits expanded) (top half of page).
2. A screenshot of the plugin's admin settings page (with individual categories limits expanded) (bottom half of page).


== Changelog ==

= 4.0.2 (2017-01-02) =
* Bugfix: Fix error in a unit test due to variable being used before being set.
* Change: Enable more error ourput for unit tests.
* Change: Default `WP_TESTS_DIR` to `/tmp/wordpress-tests-lib` rather than erroring out if not defined via environment variable.
* Change: Note compatibility through WP 4.7+.
* Change: Update copyright date (2017).

= 4.0.1 (2016-07-11) =
* New: Add class constant `SETTING_NAME` (to store setting name) and use it in `uninstall()`.
* Change: Update plugin framework to 045.
    * Ensure `reset_options()` resets values saved in the database.
* Change: Note compatibility through WP 4.6+.
* New: Add 'License' and 'License URI' header tags to readme.

= 4.0 (2016-06-22) =
Highlights:

This release revives active development of the plugin after many years and includes many, many changes. Backwards compatilibility has been maintained; it just handles things better and introduces a number of new features. Some notable changes:

* Introduced support for defining custom limits for custom post type archives.
* Now treat 'archives_paged_limit', if specified, as secondary fallback for paged limits for day, month, and year archives.
* Added fairly comprehensive unit tests.

Details:

* New: Add support for defining custom limits for custom post type archives.
* New: Add `get_individual_limit_setting_name()` as a helper function to determine the individual limit setting name for authors, categories, custom post types, and tags.
* New: Add `has_individual_limits()` to indicate if a setting type has individual limits.
* Change: Refactor `custom_post_limits()` handling for author, category, and tag individual limits.
* Change: Treat 'archives_paged_limit', if specified, as secondary fallback for paged limits for day, month, and year archives.
* Change: On settings page, show help text indicating the value source or default for all (now to include paged) limits.
* Change: Update plugin framework to 044.
* Change: Rearrange when certain hooks are registered.
* Change: Refactor `is_individual_limits_enabled()` slightly.
* Change: Improve singleton implementation.
    * Add `get_instance()` static method for returning/creating singleton instance.
    * Make static variable 'instance' private.
    * Make constructor protected.
    * Make class final.
    * Additional related changes in plugin framework (protected constructor, erroring `__clone()` and `__wakeup()`).
* Fix: Initialize private instance variable `$first_page_offset` to null in `custom_post_limits()` to avoid pollution from potential previous invocation.
* Fix: Explicitly declare `activation()` and `uninstall()` static.
* Fix: For `options_page_description()`, match method signature of parent class.
* New: Add unit tests.
* Change: Discontinue use of PHP4-style constructor.
* Change: Discontinue use of explicit pass-by-reference for objects.
* Change: Reformat plugin header.
* Change: Add support for language packs:
    * Set textdomain using a string instead of a variable.
    * Remove .pot file and /lang subdirectory.
    * Remove 'Domain Path' from plugin header.
* Change: Use explicit path when requiring plugin framework.
* Change: Prevent execution of code if file is directly accessed.
* Change: Minor code reformatting (spacing, bracing, conditional comparison order).
* Change: Minor documentation reformatting (spacing, punctuation).
* Change: Re-license as GPLv2 or later (from X11).
* New: Add 'License' and 'License URI' header tags to readme.txt and plugin file.
* New: Add LICENSE file.
* New: Add empty index.php to prevent files from being listed if web server has enabled directory listings.
* Change: Note compatibility through WP 4.5+.
* Change: Drop compatibility with version of WP older than 4.1.
* Change: Update donate link.
* Change: Update copyright date (2016).
* New: Add assets to plugin's Plugin Directory SVN repo.
    * Add plugin icon.
    * Add banner image.
    * Update screenshots.
    * Add third screenshot.
    * Remove screenshots from plugin package.

= 3.6 =
* Update plugin framework to version 034
* Fix problem where plugin settings page won't load for sites with a lot of authors, categories, and/or tags
* Fix correct_paged_offset() to only operate against main query
* By default, disable listing of limits for individual authors, categories, and tags
* Add filter 'c2c_cpl_enable_all_individual_limits' to allow enabling limits for all individual authors, categories, and tags (supersedes the specific limits)
* Add filter 'c2c_cpl_enable_all_individual_author_limits' to allow enabling limits for individual authors
* Add filter 'c2c_cpl_enable_all_individual_category_limits'  to allow enabling limits for individual categories
* Add filter 'c2c_cpl_enable_all_individual_tag_limits'  to allow enabling limits for individual tags
* Remove support for 'c2c_custom_post_limits' global
* Note compatibility through WP 3.3+
* Regenerate .pot
* Change plugin description
* Add 'Domain Path' directive to top of main plugin file
* Add link to plugin directory page to readme.txt
* Tweak installation instructions in readme.txt
* Update screenshot for WP 3.3
* Add second screenshot
* Update copyright date (2012)

= 3.5 =
* Add support for different paged (non-first page) limits for each section (each requires separate setting)
* Re-implement/fix display and saving of individual authors/categories/tags limit
* Hide individual author/category/tag limits by default (via JS, so still works for non-JSers)
* Update plugin framework to version 027
* Fix to properly register activation and uninstall hooks
* Save a static version of itself in class variable $instance
* Rename class from 'CustomPostLimits' to 'c2c_CustomPostLimits'
* Remove placeholder settings: individual_authors, individual_categories, and individual_tags
* Remove functions: register_individual_archive_options(), pre_display_option()
* Add functions: save_individual_options(), correct_paged_offset()
* Use get_users() in get_authors() rather than constructing query
* Explicitly declare all functions as public
* Note compatibility through WP 3.2+
* Drop compatibility with versions of WP older than 3.1
* Add more PHPDoc
* Update screenshot
* Regenerate .pot
* Minor tweaks to code formatting (spacing)
* Update copyright date (2011)

= 3.0 =
* Only output plugin's admin JS on its own page
* Better localization support
* Store plugin instance in global variable, $c2c_custom_post_limits, to allow for external manipulation
* Re-implementation by extending C2C_Plugin_009, which adds support for:
** Better sanitization of input values
** Offload of core/basic functionality to generic plugin framework
** Additional hooks for various stages/places of plugin operation
** Easier localization support
* Remove docs from top of plugin file (all that and more are in readme.txt)
* Note compatibility with WP 3.0+
* Minor tweaks to code formatting (spacing)
* Add Upgrade Notice section to readme.txt
* Remove trailing whitespace

= 2.6 =
* Revert post limiting back to hooking 'pre_option_posts_per_page' rather than filtering 'post_limits' (fixes bug introduced in v2.5)
* Fix bug related to individual author/category/tag limits not applying (the primary intent of the v2.5 release, but needed re-fixing due to reversion)
* Fix bug preventing value of individual limits from appearing on settings page (the value had been saved and used properly, though)
* Add 'Reset Settings' button to facilitate resetting all limits configured via the plugin
* Internal: add get_authors(), get_categories(), get_tags() to retrieve and buffer those respective values if actually needed
* Update object's option buffer after saving changed submitted by user
* Add PHPDoc documentation
* Minor documentation tweaks

= 2.5 =
* Reverted post limiting method used to filtering 'post_limits' again rather than hooking 'pre_option_posts_per_page'
* Fixed bug related to individual author/category/tag limits not applying
* Changed invocation of plugin's install function to action hooked in constructor rather than in global space
* Changed unobtrusively added JavaScript click events to return false, rather than depending on an embedded JS call in link (fixes IE8 compatibility)
* Added full support for localization
* Used admin_url() instead of hardcoded admin path
* Removed compatibility with versions of WP older than 2.8
* Noted compatibility with WP 2.9+

= 2.0 =
* Changed how post limiting is achieved by hooking 'pre_option_posts_per_page' rather than filtering 'post_limits'
* Simplified custom_post_limits()
* Changed permission check to access settings page
* Used plugins_url() instead of hardcoded path
* Removed compatibility with versions of WP older than 2.6
* Noted compatibility with WP2.8
* Began initial effort for localization
* Fixed edge-case bug causing limiting to occur when not appropriate
* Fixed bug with tag names not appearing

= 1.5 =
* NEW:
* Added ability to specify limit on a per-category, per-author, and per-tag basis
* Added ability to show all posts (i.e no limit, via a limit of -1)
* Added "Settings" link next to "Activate"/"Deactivate" link next to the plugin on the admin plugin listings page
* CHANGED:
* Tweaked plugin's admin options page to conform to newer WP 2.7 style
* Extended compatibility to WP 2.7+
* Updated installation instructions, extended description, copyright
* Facilitated translation of some text
* Memoized options
* In admin options page, due to difference b/w WP <2.5 and >2.5, link text for options page is just referred to as "here"
* FIXED:
* Prevent post limiting from occurring in the admin listing
* Fixed plugin path problem in recent versions of WP
* Fixed post paging (next_posts_link()/previous_posts_link()) was not taking post limit into account

= 1.0 =
* Initial release


== Upgrade Notice ==

= 4.0.2 =
Trivial update: updated unit test bootstrap file, noted compatibility through WP 4.7+, and updated copyright date

= 4.0.1 =
Minor update: noted compatibility through WP 4.6+; updated plugin framework.

= 4.0 =
Recommended major update: added support to set limits for custom post types; 'archives_paged_limit' is now a fallback for paged date archives; compatibility is now WP 4.1-4.5; added unit tests; lots of backend improvements.

= 3.6 =
Recommended update: disabled support for individual archive limits by default (configurable) to help sites with lots of authors/categories/tags; noted compatibility through WP 3.3+; updated plugin framework; and more.

= 3.5 =
Recommended update: support different non-first-page limits; re-implemented display/handling of individual category/tag/author limits; noted compatibility through WP 3.2+, drop compatibility with WP 3.0; and more.

= 3.0 =
Recommended update. Highlights: verified WP 3.0 compatibility.
