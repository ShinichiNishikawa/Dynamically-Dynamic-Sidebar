<?php

/**
 * Get the registered custome sidebar areas.
 *
 * With this registration,
 *
 *
 * https://developer.wordpress.org/reference/functions/register_sidebar/
 *
 */
add_action( 'widgets_init', 'dds_widgets_init' );
function dds_widgets_init() {

	$dds_sidebars = get_option( 'dds_sidebars' );
	if( $dds_sidebars && is_array( $dds_sidebars ) ){
		foreach ( $dds_sidebars as $key => $val ) {
			register_sidebar(
				array(
					'name' => $val,
					'id' => $key,
				)
			);
		}
	}

}


/**
 * Switch the sidebar area.
 *
 * Get the desired area and swith it with
 * the area to be dissappear.
 *
 * The actual rendering of the sidebar area
 * is done by `do_action( 'dynamically_dynamic_sidebar' );`.
 * What this function does is
 *
 * - Get the new area.
 * - Set the target: Retrieve which sidebar area should be switched to a new one.
 * - Get html info:  Get the settings of the surrouding htmls of the area, such as `before_widget`.
 * - Set the html settings: Apply the htmls into the new one.
 * - Stop the default: Stop rendering the default area.
 * - Render: Render the new one.
 */
add_filter( 'is_active_sidebar', 'dds_switch_sidebar', 10, 2 );
function dds_switch_sidebar( $is_active_sidebar, $index ) {


	// Retrieve the desired area from post type, term, or post.
	$switch = dds_get_desired_widget_area();

	// If none is set, do nothing.
	if ( false == $switch ) {
		return $is_active_sidebar;
	}

	// Retrieve the target area.
	$dds_target = get_option( 'dds_target_widget_area' );

	// If none is set, do nothing.
	if ( ! $dds_target ) {
		return $is_active_sidebar;
	}

	// Act only when the the $index is the target area.
	if ( $dds_target === $index ) {

		// Retrieve the display parameters from the original
		// and set them to the new area.
		global $wp_registered_sidebars;
		if ( isset( $wp_registered_sidebars[$index] ) ) {

			$original_params = $wp_registered_sidebars[$index]; // the settings here.

		} else {

			// !isset happens when user changes the theme and
			// WP loses widget area id.
			return $is_active_sidebar;

		}

		// Set the four html parameters.
		$params = array(
			'before_widget',
			'after_widget',
			'before_title',
			'after_title',
		);
		foreach ( $params as $p ) {

			$wp_registered_sidebars[$switch][$p] = $original_params[$p];

		}

		// Stop the default
		$is_active_sidebar = false;

		// Rendering. output the dynamic one.
		do_action( 'dynamically_dynamic_sidebar' );

	}

	return $is_active_sidebar;

}

/**
 * This is the rendering part.
 *
 * - Get the new widget.
 * - Check if it's when.
 * - Render.
 *
 */
add_action( 'dynamically_dynamic_sidebar', 'dynamically_dynamic_sidebar' );
function dynamically_dynamic_sidebar() {

	// dds_get_desired_widget_area get the id from various cases.
	$switch = dds_get_desired_widget_area();

	if ( false == $switch ) {
		return;
	}

	// The list of the areas that the user set.
	$registered  = get_option( 'dds_sidebars' );

	// If the area post, term, or post type assigns exist,
	// render the sidebar area.
	if ( array_key_exists( $switch, $registered ) ) {

		// Trick to prevent is_active_sidebar loop
		remove_filter( 'is_active_sidebar', 'dds_switch_sidebar' );

		// Check if widgets are set into the area.
		if ( is_active_sidebar( $switch ) ) {
			dynamic_sidebar( $switch );
		}

		// Get the filter back here.
		add_filter( 'is_active_sidebar', 'dds_switch_sidebar', 10, 2 );

	} else {

		// If anything goes wrong, the default.
		dynamic_sidebar( 1 );

	}

}

//
/**
 * Returns the widget area id (ie, sidebar-custom, sidebar-noAds)
 *
 * The priority goes as listed below.
 *
 * 1. The area set by post comes first.
 * 2. The area set by temrs comes next.
 * 3. The area set by post type comes the third.
 * 4. The default area comes last.
 *
 * The program flow goes as listed below.
 *
 * 1. Check the is_singluar().
 *    1-1. area set to the post.
 * 	  1-2. area set to the term assigned to the post.
 *    1-3. area set to the post type.
 * 2. Check the taxonomy term archives.
 *    2-1. area set to the term.
 *    2-2. area set to the parent term of the term.
 *    // 2-3. area set to the taxonomy.
 *    2-4. area set to the post type.
 * 3. Check the post type archive.
 *
 */
function dds_get_desired_widget_area() {

	$widget_area = false;

	// 1. When the page is_singular().
	if ( is_singular() ) {

		global $post;
		// 1-1. area is saved in the post_meta.
		$widget_area = get_post_meta( $post->ID, 'dds_widget_area', true );
		if ( $widget_area ) {
			return $widget_area;
		}

		// 1-2. get the area id set to the term or the ancestor of the term.
		$by_term = dds_get_widget_of_post_by_term( $post );
		if ( $by_term ) {
			return $by_term["area-id"];
		}

		// 1-3. get the area id set to the post type of the post shown now.
		$by_post_type = dds_get_widget_of_post_type( $post );
		if ( $by_post_type ) {
			return $by_post_type;
		}

	} elseif ( is_category() || is_tag() || is_tax() ) {

		// This is the term object which used in the query.
		$queried_obj = get_queried_object();

		// The id of the term.
		$term_id     = $queried_obj->term_id;

		// Chekc if the term has a custom sidebar area in its term meta.
		$widget_area = get_term_meta( $term_id, 'dds_widget_area', true );

		// If a custome area exists, retun it.
		if ( $widget_area ) {
			return $widget_area;
		}

		// If not, check the ancestors and return the found one.
		$ancestors = get_ancestors( $term_id, $queried_obj->taxonomy );
		if ( is_array( $ancestors ) ) {
			$widget_area_arr = dds_check_term_arrays_allocated_area( $ancestors );
			$widget_area = $widget_area_arr["area-id"];
			return $widget_area;
		}

	} elseif ( is_post_type_archive() ) {

		$post_type = get_post_type();

		$widget_area_arr = get_option( "dds_area_for_post_types" );

		$widget_area = $widget_area_arr[$post_type];

		if ( "dds-default" !== $widget_area ) {
			return $widget_area;
		}

	}

	return false;

}
