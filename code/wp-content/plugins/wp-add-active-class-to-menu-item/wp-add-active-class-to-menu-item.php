<?php
/*
  Plugin Name: WP Add Active Class To Menu Item
  Plugin URI: https://www.aftabmuni.wordpress.com
  Description: This plugin is useful when you need to add active class to menu item li tag in place of current-menu-item or current_page_parent
  Author: Aftab Muni
  Version: 1.0
  Author URI: https://www.aftabmuni.wordpress.com
 */

/*
  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.
 */

if (!defined('ABSPATH')) {
    exit;
}

include 'functions.php';

define('AMM_WPACTMI_VERSION', '1.0');
define('AMM_WPACTMI_NAME', 'WP Add Active Class To Menu Item');
define('AMM_WPACTMI_SLUG', 'wp_add_active_class_to_menu_item');
define('AMM_WPACTMI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AMM_WPACTMI_PLUGIN_DIR', plugin_dir_path(__FILE__));

add_action('admin_menu', 'amm_wpactmi_menu');

if (!get_option('wp_add_active_class_to_menu_item_classes')) {
    add_option('wp_add_active_class_to_menu_item_classes', 'active');
}

function amm_wpactmi_menu() {
    add_options_page(
            AMM_WPACTMI_NAME, AMM_WPACTMI_NAME, 'manage_options', AMM_WPACTMI_SLUG . '_dashboard', AMM_WPACTMI_SLUG . '_settings'
    );
}

add_filter('nav_menu_css_class', 'amm_wpactmi_active_li_class', 10, 2);

function amm_wpactmi_active_li_class($classes, $item) {
    if (in_array('current-menu-item', $classes) || in_array('current_page_parent', $classes)) {
        $temp_class = str_replace(",", " ", trim(get_option('wp_add_active_class_to_menu_item_classes')));
        $classes[] = $temp_class;
    }
    return $classes;
}
