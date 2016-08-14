<?php

// 各タクソノミで出力と保存をするためにフックで登録
/**
 * Hooks the actions to display and save
 * fields on the term list/edit screens.
 *
 */
add_action( 'admin_init', 'dds_do_meta_for_all_taxonomies' );
function dds_do_meta_for_all_taxonomies() {

	$taxonomies = dds_get_taxonomies();

	foreach ( $taxonomies as $t ) {
		add_action( $t . '_add_form_fields', 'dds_term_add_meta_fields'  ); // output on add screen
		add_action( $t . '_edit_form',       'dds_term_edit_meta_fields' ); // output on edit screen
		add_action( 'created_' . $t,         'dds_update_term_meta'      ); // fires on add screen
		add_action( 'edited_'  . $t,         'dds_update_term_meta'      ); // fires on edit screen
	}

}


/**
 * Output the form on edit screen.
 *
 */
function dds_term_edit_meta_fields( $taxonomy ) {

	// ユーザーが作成したウィジェットエリア
	$registered = get_option( 'dds_sidebars' );
	$the_area   = get_term_meta( $taxonomy->term_id, 'dds_widget_area', true );
	?>
	<table class="form-table">
		<tr class="form-field">
			<th scope="row"><?php _e( 'Dynamically Dynamic Sidebar', 'dynamically-dynamic-sidebar' ); ?></th>
			<td>
				<select name="dds_widget_area" id="dds_widget_area">
					<option value=""><?php esc_html_e( '- Choose a widget Area', 'dynamically-dynamic-sidebar' ); ?></option>
					<?php foreach ( $registered as $key => $val ) { ?>
						<option
						value="<?php echo esc_attr( $key ); ?>"
						<?php if( $key===$the_area ) { echo ' selected="selected"'; } ?>
						>
							<?php echo esc_html( $val ); ?>
						</option>
					<?php } ?>
				</select>
			</td>
		</tr>
	</table>
	<?php
	wp_nonce_field( "dds_term_meta_field", "dds_term_meta_nonce" );

}

/**
 * Output the form on add screen.
 *
 */
function dds_term_add_meta_fields( $taxonomy ) {

	// ユーザーが作成したウィジェットエリア
	$registered = get_option( 'dds_sidebars' );
	?>
	<div class="form-field">
		<label for="dds_widget_area"><?php _e( 'Dynamically Dynamic Sidebar', 'dynamically-dynamic-sidebar' ); ?></label>
		<select name="dds_widget_area" id="dds_widget_area">
			<option value=""><?php esc_html_e( '- Choose a widget Area', 'dynamically-dynamic-sidebar' ); ?></option>
			<?php foreach ( $registered as $key => $val ) { ?>
				<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $val ); ?></option>
			<?php } ?>
		</select>
	</div>
	<?php
	wp_nonce_field( "dds_term_meta_field", "dds_term_meta_nonce" );

}


/**
 * Save the term meta on save.
 *
 */
function dds_update_term_meta( $term_id ) {

	if ( !$_POST["dds_term_meta_nonce"] ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['dds_term_meta_nonce'], 'dds_term_meta_field' ) ) {
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

	update_term_meta( $term_id, 'dds_widget_area', $posted );

}

/**
 * Add a column to the list table of the terms.
 *
 */
add_action( 'admin_init', 'dds_fire_term_table_funcs' );
function dds_fire_term_table_funcs() {

	$taxonomies = dds_get_taxonomies();
	foreach ( $taxonomies as $taxonomy ) {
		add_filter( 'manage_edit-' . $taxonomy . '_columns',  'dds_add_term_table_column'       ); // ヘッダーを入れて定義
		add_filter( 'manage_' . $taxonomy . '_custom_column', 'dds_add_term_table_cells', 10, 3 ); // 中身
	}

}

/**
 * Output the th
 */
function dds_add_term_table_column( $columns ) {

	$columns["dds_widget_column"] = __( 'Sidebar', 'dynamically-dynamic-sidebar' );
	return $columns;

}

/**
 * Output the allocated area name in the cells.
 */
function dds_add_term_table_cells( $content, $column_name, $term_id ) {

	if ( 'dds_widget_column' === $column_name ) {
		$allocated_widget_key = get_term_meta( $term_id, 'dds_widget_area', true );
		if ( $allocated_widget_key ) {
			$allocatable_widgets  = get_option( 'dds_sidebars' );
			$content = $allocatable_widgets[$allocated_widget_key];
		}
	}

	return $content;

}
