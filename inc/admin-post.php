<?php

add_action( 'add_meta_boxes', 'dds_add_meta_box' );
function dds_add_meta_box() {

	if ( ! get_option( 'dds_sidebars' ) ) {
		return;
	}

	$args = array(
		'public' => true,
	);
	$post_types = get_post_types( $args );
	unset( $post_types["attachment"] );

	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'dynamically-dynamic-sidebar',                                      // id
			__( 'Dynamically Dynamic Sidebar', 'dynamically-dynamic-sidebar' ), // section title
			'dds_post_meta_boxes',                                              // callback func
			$post_type,                                                         // screen
			'side'                                                              // position in post edit page
		 );
	}

}

// ポスト用のメタボックス
function dds_post_meta_boxes( $post ) {

	$registered = get_option( 'dds_sidebars' );
	$the_area   = get_post_meta( $post->ID, 'dds_widget_area', true );

	dds_alert_term_widget( $post );

	?>
	<select name="dds_widget_area" id="dds_widget_area">
		<option value=""><?php esc_html_e( '- Choose a widget area', 'dynamically-dynamic-sidebar' ); ?></option>
		<?php foreach ( $registered as $key => $val ) { ?>
			<option
			value="<?php echo esc_attr( $key ); ?>"
			<?php if( $key===$the_area ) { echo ' selected="selected"'; } ?>
			>
				<?php echo esc_html( $val ); ?>
			</option>
		<?php } ?>
	</select>
	<?php
	wp_nonce_field( "dds_post_metabox", "dds_post_metabox_nonce" );

}

function dds_alert_term_widget( $post ) {

	$widget_by_term = dds_get_widget_of_post_by_term( $post );

	if ( $widget_by_term ) {

		$format = __( '<p class="dds-notice">The widget area for this post is <strong>%1$s</strong>, which is allocated to <strong>"%2$s"</strong> in <strong>"%3$s"</strong>.</p>', 'dynamically-dynamic-sidebar' );

		printf(
			esc_html( $format ),
			esc_html( $widget_by_term["area-name"] ),
			esc_html( $widget_by_term["term"]->name ),
			esc_html( $widget_by_term["term"]->taxonomy )
		);

		echo '<p class="dds-notice">You can override it by choosing another one here.</p>';

	} else {

		echo '<p class="dds-notice">You can switch the sidebar area for this post.</p>';

	}

}


add_action( 'save_post', 'dds_save_post_widget' );
function dds_save_post_widget( $post_id ) {

	if ( !$_POST["dds_post_metabox_nonce"] ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['dds_post_metabox_nonce'], 'dds_post_metabox' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$posted = $_POST["dds_widget_area"];
	if ( !isset( $posted ) ) {
		return;
	} else {
		$posted = sanitize_title( $posted );
	}

	update_post_meta( $post_id, 'dds_widget_area', $posted );

}

// posts and custom post types
add_filter( 'manage_posts_columns',            'dds_add_posts_table_column'       );
add_action( 'manage_posts_custom_column',      'dds_add_posts_table_cells', 10, 2 );

// page
add_filter( 'manage_page_posts_columns',       'dds_add_posts_table_column', 10);
add_action( 'manage_page_posts_custom_column', 'dds_add_posts_table_cells', 10, 2);

function dds_add_posts_table_column( $columns ) {
	$columns["dds_widget_column"] = "Sidebar";
	return $columns;
}

function dds_add_posts_table_cells( $column_name, $post_id ) {

	if ( 'dds_widget_column' !== $column_name ) {
		return;
	}

	$widget_by_post = get_post_meta( $post_id, 'dds_widget_area', true );
	$widget_name    = dds_get_widget_name_by_id($widget_by_post);

	if ( $widget_name ) {

		echo esc_html( "<strong>{$widget_name}</strong>" );

	} elseif ( $widget_by_term = dds_get_widget_of_post_by_term( $post_id ) ) {

		$format = '<strong>%1$s</strong><br>(from %2$s of %3$s )';
		printf(
			esc_html( $format ),
			esc_html( $widget_by_term["area-name"] ),
			esc_html( $widget_by_term["term"]->name ),
			esc_html( $widget_by_term["term"]->taxonomy )
		);

	}

}
