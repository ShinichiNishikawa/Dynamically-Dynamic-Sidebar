=== Dynamically Dynamic Sidebar ===
Contributors: ShinichiN
Tags: widget, widget area, sidebar
Requires at least: 4.4
Tested up to: 3.4
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create unlimited custom sidebar/widget areas and switch existing areas depending on post, page, custom-post-type post, categories, tags and custom taxonomy terms. You can do this without touching your theme.

== Description ==

This plugin enables you to create unlimited custom sidebar/widget areas and switch existing areas. You can do this without touching your theme.

This plugin utilizes term meta api and so it works only with WordPress 4.4 and above.

Your theme needs to use `is_active_sidebar()` when calling `dynamic_sidebar()` function.

This will work.

`if ( is_active_sidebar( 'sidebar-1' ) ) {
	dynamic_sidebar( 'sidebar-1' );
}`

This doesn't work.

`
dynamic_sidebar( 'sidebar-1' );
`

If you only want to output your custom sidebar, put this code on where you want to display the sidebar in your theme.

`do_action( 'dynamically_dynamic_sidebar' );`


== Installation ==

1. Upload `dynamically-dynamic-sidebar` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to `/wp-admin/themes.php?page=dynamically-dynamic-sidebar`, which is located under `Appearance`

== Frequently Asked Questions ==

= How many widget areas can I create? =

Unlimited.

= How do I switch existing sidebar or widget area? =

Choose the target widget area in admin panel. The admin page for this plugin is located at Admin > Appearance > Dynamically Dynamic Sidebar.

You need to make sure that the call of dynamic_sidebar is properly wrapped with is_active_sidebar() conditional tag.

`
`if ( is_active_sidebar( 'sidebar-1' ) ) {
	dynamic_sidebar( 'sidebar-1' );
}`
`

Or you can just output your dynamically created sidebar with `do_action( 'dynamically_dynamic_sidebar' );`, too.


== Screenshots ==


== Changelog ==

= 0.1 =
* Released on github
