<?php
/*
Plugin Name: Dynamically Dynamic Sidebar
Version: 0.7
Description: This plugin enables you to create unlimited widget area and use them for posts, pages, categories, tags and so on.
Author: Shinichi Nishikawa
Author URI: http://nobil.cc
Text Domain: dynamically-dynamic-sidebar
Domain Path: /languages
*/


$dds_wp_version = $GLOBALS['wp_version'];

// This plugin requires WP version greater than 4.4.
if ( version_compare( (float)$dds_wp_version, '4.4', '<' ) ) {

	add_action( 'admin_notices', 'dds_admin_error_notice' );
	return false;

} else {

	define( 'DDS_PATH', plugin_dir_path( __FILE__ ) );

	require DDS_PATH . 'inc/main.php';
	require DDS_PATH . 'inc/admin-main.php';
	require DDS_PATH . 'inc/admin-post.php';
	require DDS_PATH . 'inc/admin-term.php';
	require DDS_PATH . 'inc/functions.php';

}

function dds_admin_error_notice() {
	?><div class="error"><p><?php _e( '"Dynamically Dynamic Sidebar" plugin utilizes Term Meta API, which was introduced in WordPress version 4.4. You need to upgrade your WordPress to use this plugin.', 'dynamically-dynamic-sidebar' ); ?></p></div><?php
}
