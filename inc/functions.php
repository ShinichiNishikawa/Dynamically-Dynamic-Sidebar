<?php

/**
 * Returns the taxonomies which are public.
 *
 */
function dds_get_taxonomies() {

	// コンテンツの分類に使うタクソノミに限定
	$args = array(
		'public'  => true,
		'show_ui' => true,
	);

	$taxonomies = get_taxonomies( $args );

	// カテゴリを最後にしたい。他のものから上書きしそうだから。
	$reversed   = array_reverse( $taxonomies );

	return $reversed;

}



/**
 * Given the post ID, looking into the post type of the post,
 * returns an array of the area FIRST FOUND.
 * If no area is assigned, returns false.
 *
 * @param  int   $post          post id
 * @return array $area_term_arr an array of widget_id, widget_name
                                and term object detemining the widget id.
 */
function dds_get_widget_of_post_type( $post ) {

	if ( ! $post ) {
		return false;
	}

	$dds_post_type = $post->post_type;
	$registered_posttypes_widgets = get_option( "dds_area_for_post_types" );
	if ( isset( $registered_posttypes_widgets[$dds_post_type] ) ) {
		$by_post_type = $registered_posttypes_widgets[$dds_post_type];
	} else {
		$by_post_type = false;
	}

	return $by_post_type;


}


/**
 * Given the post ID, looking into the terms of the post,
 * returns an array of the area FIRST FOUND.
 * If no area is assigned, returns false.
 *
 * @param  int   $post          post id
 * @return array $area_term_arr an array of widget_id, widget_name
                                and term object detemining the widget id.
 */
function dds_get_widget_of_post_by_term( $post ) {

	// Init
	$terms         = array();
	$ancestors     = array();
	$area_term_arr = array();

	// Getting all the taxonomies, which are public and has the UI.
	$taxonomies = dds_get_taxonomies();

	// Loop through all the taxonomy terms.
	foreach ( $taxonomies as $taxonomy ) {

		$term_obj = get_the_terms( $post, $taxonomy ); // Get "THE" terms.

		// タームに属していればマスターに入れる
		if ( is_array( $term_obj ) ) {
			$terms = array_merge( $terms, $term_obj );
		}

	}

	// Pass the term array and get the widget area back.
	$area_term_arr = dds_check_term_arrays_allocated_area( $terms );

	if ( $area_term_arr ) {
		return $area_term_arr;
	}

	// Check the ancestors too.
	foreach ( $terms as $t ) {

		$ancestor_term_id_arr = get_ancestors( $t->term_id, $t->taxonomy );

		if ( is_array( $ancestor_term_id_arr ) ) {
			foreach ( $ancestor_term_id_arr as $a_id ) {
				$term_obj = get_term_by( 'id', $a_id, $t->taxonomy );
				$ancestors[] = $term_obj;
			}
		}

	}

	// Pass the parent term array and get the widget area back.
	$area_term_arr = dds_check_term_arrays_allocated_area( $ancestors );

	if ( $area_term_arr ) {
		return $area_term_arr;
	} else {
		return false;
	}

}

/**
 * Given an array of term objects,
 * returns FIRST FOUND widget area info.
 *
 *
 * @param  int|object $terms         term id or term object
 * @return array      $area_term_arr $area_term_arr an array of widget_id, widget_name
                                and term object detemining the widget id.
 */
function dds_check_term_arrays_allocated_area( $terms ) {

	if ( !isset( $terms ) || empty( $terms ) || !is_array( $terms ) ) {
		return false;
	}

	foreach ( $terms as $t ) {

		// Let's handle ids only.
		if ( is_object( $t ) ) {
			$term_id = $t->term_id;
		} elseif ( is_int( $t ) ) {
			$term_id = $t;
		}

		$area_id = get_term_meta( $term_id, 'dds_widget_area', true );

		if ( $area_id ) { // if exists, create an array and escape from the loop.

			// the array of the user custom areas.
			$allocatable_widgets  = get_option( 'dds_sidebars' );

			$area_term_arr["area-id"]   = $area_id;
			$area_term_arr["area-name"] = $allocatable_widgets[$area_id];
			$area_term_arr["term"]      = $t;

			break;

		}

	}

	if ( isset( $area_term_arr ) && is_array( $area_term_arr ) ) {
		return $area_term_arr;
	} else {
		return false;
	}

}


function dds_get_widget_name_by_id( $w_id ) {

	if ( !$w_id ) {
		return false;
	}

	$user_define_widgets  = get_option( 'dds_sidebars' );
	if ( array_key_exists( $w_id, $user_define_widgets ) ) {
		return $user_define_widgets[$w_id];
	}

	return false;

}
