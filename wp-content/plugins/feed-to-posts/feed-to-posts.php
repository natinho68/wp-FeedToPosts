<?php
/*
Plugin Name: Feed to posts
Description: Create posts automatically from JSON feed
Version: 0.1
Author: Nathan MEYER
Author URI: https://github.com/natinho68
License: GNU GENERAL PUBLIC LICENSE v2
*/


// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

// Include the dependencies needed to instantiate the plugin.
foreach (glob(plugin_dir_path(__FILE__) . 'admin/*.php') as $file) {
    include_once $file;
}

// Include the shared dependency.
include_once(plugin_dir_path(__FILE__) . 'shared/class-deserializer.php');
include_once(plugin_dir_path(__FILE__) . 'public/class-feed-parser.php');
include_once(ABSPATH . 'wp-admin/includes/post.php');

add_action('plugins_loaded', 'custom_admin_settings');

/**
 * Starts the plugin.
 *
 */
function custom_admin_settings()
{
    $serializer = new Serializer();
    $serializer->init();

    $deserializer = new Deserializer();

    $plugin = new Submenu(new Submenu_Page($deserializer));
    $plugin->init();

    $notices = new JP_Easy_Admin_Notices();
    $notices->init();

    $public = new Feed_Parser($serializer, $deserializer, $notices);
    $public->postFromFeed();
}